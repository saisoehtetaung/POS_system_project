<?php

namespace App\Domain\Sales\Services;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Customers\Models\Customer;
use App\Domain\Customers\Models\CustomerLedger;
use App\Domain\Identity\Models\Shop;
use App\Domain\Identity\Models\Terminal;
use App\Domain\Inventory\Models\StockBalance;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Sales\Models\Payment;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Models\SaleItem;
use App\Domain\Shifts\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * @param  array<int, array{variant_id:string,unit_id:?string,unit_name:?string,qty:float|int,unit_price:float|int,factor_to_base:float|int}>  $cart
     */
    public function checkout(
        User $cashier,
        array $cart,
        ?string $customerId,
        bool $useCredit,
        float $amountPaid
    ): Sale {
        if ($cart === []) {
            throw ValidationException::withMessages([
                'cart' => 'Add at least one item before checkout.',
            ]);
        }

        $shop = Shop::query()->whereKey($cashier->shop_id)->firstOrFail();
        $terminal = $this->resolveTerminal($shop->id);
        $shift = $this->resolveOpenShift($shop->id, $terminal->id, $cashier);

        $customer = null;
        if ($customerId) {
            $customer = Customer::query()
                ->where('shop_id', $shop->id)
                ->where('is_active', true)
                ->whereKey($customerId)
                ->first();

            if (! $customer) {
                throw ValidationException::withMessages([
                    'customer_id' => 'Selected customer was not found.',
                ]);
            }
        }

        if ($useCredit && ! $customer) {
            throw ValidationException::withMessages([
                'use_credit' => 'Select a customer before using credit.',
            ]);
        }

        return DB::transaction(function () use ($cashier, $cart, $customer, $useCredit, $amountPaid, $shop, $terminal, $shift) {
            $lines = $this->buildValidatedLines($shop->id, $cart);
            $subtotal = round(collect($lines)->sum('line_total'), 2);
            $taxRate = (float) $shop->tax_rate;
            $taxAmount = $taxRate > 0 ? round($subtotal * ($taxRate / 100), 2) : 0.0;
            $total = round($subtotal + $taxAmount, 2);

            if ($useCredit) {
                $paid = max(0, min($total, round($amountPaid, 2)));
            } else {
                $paid = $total;
            }

            $due = round($total - $paid, 2);

            if ($useCredit && $due > 0) {
                $available = max(0, (float) $customer->credit_limit - (float) $customer->balance);
                if ($due > $available + 0.0001) {
                    throw ValidationException::withMessages([
                        'amount_paid' => 'Credit exceeds available limit ('.number_format($available, 0).' MMK).',
                    ]);
                }
            }

            $paymentStatus = $due <= 0
                ? 'paid'
                : ($paid <= 0 ? 'credit' : 'partial');

            $sale = Sale::query()->create([
                'shop_id' => $shop->id,
                'terminal_id' => $terminal->id,
                'shift_id' => $shift->id,
                'customer_id' => $customer?->id,
                'cashier_id' => $cashier->id,
                'sale_no' => $this->nextSaleNo($terminal->code),
                'status' => 'completed',
                'price_tier' => 'retail',
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'discount_percent' => 0,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'amount_paid' => $paid,
                'amount_due' => $due,
                'payment_status' => $paymentStatus,
                'sold_at' => now(),
            ]);

            foreach ($lines as $line) {
                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'variant_id' => $line['variant']->id,
                    'product_id' => $line['variant']->product_id,
                    'description' => $line['description'],
                    'quantity_base' => $line['quantity_base'],
                    'unit_price' => $line['unit_price'],
                    'price_tier' => 'retail',
                    'discount_amount' => 0,
                    'line_total' => $line['line_total'],
                    'is_bundle' => $line['variant']->product->product_type === 'bundle',
                ]);

                if ($line['track_stock']) {
                    $this->deductStock(
                        $shop->id,
                        $line['variant'],
                        $line['quantity_base'],
                        $sale,
                        $cashier->id
                    );
                }
            }

            if ($paid > 0) {
                Payment::query()->create([
                    'sale_id' => $sale->id,
                    'customer_id' => $customer?->id,
                    'shift_id' => $shift->id,
                    'method' => 'cash',
                    'amount' => $paid,
                    'tendered' => $paid,
                    'change_amount' => 0,
                    'paid_at' => now(),
                    'created_by' => $cashier->id,
                ]);
            }

            if ($due > 0 && $customer) {
                Payment::query()->create([
                    'sale_id' => $sale->id,
                    'customer_id' => $customer->id,
                    'shift_id' => $shift->id,
                    'method' => 'credit',
                    'amount' => $due,
                    'paid_at' => now(),
                    'created_by' => $cashier->id,
                ]);

                $customer->balance = round((float) $customer->balance + $due, 2);
                $customer->save();

                CustomerLedger::query()->create([
                    'customer_id' => $customer->id,
                    'sale_id' => $sale->id,
                    'payment_id' => null,
                    'entry_type' => 'credit_sale',
                    'debit' => $due,
                    'credit' => 0,
                    'balance_after' => $customer->balance,
                    'occurred_at' => now(),
                    'created_by' => $cashier->id,
                ]);
            }

            return $sale->load(['items', 'cashier', 'customer', 'shop']);
        });
    }

    /**
     * @param  array<int, array{variant_id:string,unit_id:?string,unit_name:?string,qty:float|int,unit_price:float|int,factor_to_base:float|int}>  $cart
     * @return array<int, array{variant:ProductVariant,description:string,quantity_base:float,unit_price:float,line_total:float,track_stock:bool}>
     */
    private function buildValidatedLines(string $shopId, array $cart): array
    {
        $lines = [];
        $neededByVariant = [];

        foreach ($cart as $index => $item) {
            $variantId = $item['variant_id'] ?? null;
            $qty = (float) ($item['qty'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $factor = max(0.000001, (float) ($item['factor_to_base'] ?? 1));
            $unitName = $item['unit_name'] ?? 'Unit';

            if (! $variantId || $qty <= 0) {
                throw ValidationException::withMessages([
                    "cart.$index" => 'Invalid cart line.',
                ]);
            }

            $variant = ProductVariant::query()
                ->with('product')
                ->whereKey($variantId)
                ->where('is_active', true)
                ->whereHas('product', fn ($query) => $query->where('shop_id', $shopId)->where('is_active', true))
                ->first();

            if (! $variant) {
                throw ValidationException::withMessages([
                    "cart.$index" => 'A product in the cart is no longer available.',
                ]);
            }

            $quantityBase = round($qty * $factor, 3);
            $trackStock = (bool) $variant->product->track_stock;

            if ($trackStock) {
                $neededByVariant[$variant->id] = ($neededByVariant[$variant->id] ?? 0) + $quantityBase;
            }

            $lines[] = [
                'variant' => $variant,
                'description' => trim($variant->product->name.' · '.$variant->name.' · '.$unitName),
                'quantity_base' => $quantityBase,
                'unit_price' => round($unitPrice, 2),
                'line_total' => round($qty * $unitPrice, 2),
                'track_stock' => $trackStock,
            ];
        }

        foreach ($neededByVariant as $variantId => $needed) {
            $balance = StockBalance::query()
                ->where('shop_id', $shopId)
                ->where('variant_id', $variantId)
                ->lockForUpdate()
                ->first();

            $onHand = (float) ($balance?->qty_on_hand ?? 0);
            if ($needed > $onHand + 0.0001) {
                $variant = collect($lines)->firstWhere(fn ($line) => $line['variant']->id === $variantId)['variant'];
                throw ValidationException::withMessages([
                    'stock' => $variant->product->name.' is out of stock (need '.$needed.', have '.$onHand.').',
                ]);
            }
        }

        return $lines;
    }

    private function deductStock(
        string $shopId,
        ProductVariant $variant,
        float $quantityBase,
        Sale $sale,
        string $userId
    ): void {
        $balance = StockBalance::query()->firstOrCreate(
            ['shop_id' => $shopId, 'variant_id' => $variant->id],
            ['qty_on_hand' => 0]
        );

        $balance = StockBalance::query()
            ->whereKey($balance->id)
            ->lockForUpdate()
            ->firstOrFail();

        $delta = -1 * abs($quantityBase);
        $balance->qty_on_hand = round((float) $balance->qty_on_hand + $delta, 3);

        if ($balance->qty_on_hand < -0.0001) {
            throw ValidationException::withMessages([
                'stock' => $variant->product->name.' stock became insufficient during checkout.',
            ]);
        }

        $balance->save();

        StockMovement::query()->create([
            'shop_id' => $shopId,
            'variant_id' => $variant->id,
            'movement_type' => 'sale_out',
            'qty_delta' => $delta,
            'qty_after' => $balance->qty_on_hand,
            'unit_cost' => $variant->cost_price,
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
            'reason' => 'POS sale '.$sale->sale_no,
            'occurred_at' => now(),
            'created_by' => $userId,
        ]);
    }

    private function resolveTerminal(string $shopId): Terminal
    {
        $terminal = Terminal::query()
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->orderBy('code')
            ->first();

        if ($terminal) {
            return $terminal;
        }

        return Terminal::query()->create([
            'shop_id' => $shopId,
            'code' => 'CS-01',
            'name' => 'Front Counter 1',
            'is_active' => true,
        ]);
    }

    private function resolveOpenShift(string $shopId, string $terminalId, User $cashier): Shift
    {
        $shift = Shift::query()
            ->where('shop_id', $shopId)
            ->where('terminal_id', $terminalId)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if ($shift) {
            return $shift;
        }

        return Shift::query()->create([
            'shop_id' => $shopId,
            'terminal_id' => $terminalId,
            'user_id' => $cashier->id,
            'status' => 'open',
            'opened_at' => now(),
            'opening_cash' => 0,
            'notes' => 'Auto-opened for POS checkout',
        ]);
    }

    private function nextSaleNo(string $terminalCode): string
    {
        $prefix = strtoupper($terminalCode).'-'.now()->format('Ymd').'-';

        $latest = Sale::query()
            ->where('sale_no', 'like', $prefix.'%')
            ->orderByDesc('sale_no')
            ->value('sale_no');

        $sequence = 1;
        if (is_string($latest) && preg_match('/-(\d+)$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
