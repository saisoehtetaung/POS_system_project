<?php

namespace App\Domain\Purchasing\Services;

use App\Domain\Inventory\Models\StockBalance;
use App\Domain\Inventory\Models\StockLot;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Purchasing\Models\Purchase;
use App\Domain\Purchasing\Models\PurchaseItem;
use App\Domain\Purchasing\Models\SupplierPayment;
use App\Domain\Purchasing\Models\SupplierPriceHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReceivePurchaseService
{
    public function receive(Purchase $purchase, User $actor): Purchase
    {
        if ($purchase->status !== 'draft') {
            throw ValidationException::withMessages([
                'purchase' => 'Only draft purchases can be received.',
            ]);
        }

        if ($purchase->items()->count() === 0) {
            throw ValidationException::withMessages([
                'purchase' => 'Add at least one line before receiving.',
            ]);
        }

        if ($purchase->payment_type === 'credit' && ! $purchase->due_date) {
            throw ValidationException::withMessages([
                'due_date' => 'Credit purchases require a due date.',
            ]);
        }

        return DB::transaction(function () use ($purchase, $actor) {
            $purchase = Purchase::query()
                ->with('items')
                ->whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($purchase->status !== 'draft') {
                throw ValidationException::withMessages([
                    'purchase' => 'Only draft purchases can be received.',
                ]);
            }

            foreach ($purchase->items as $item) {
                $this->applyStockIn($purchase, $item, $actor);
                $this->recordPriceHistory($purchase, $item);
            }

            $amountPaid = $purchase->payment_type === 'cash' ? (float) $purchase->total : 0.0;

            $purchase->update([
                'status' => 'received',
                'purchased_at' => now(),
                'amount_paid' => $amountPaid,
            ]);

            if ($purchase->payment_type === 'cash' && $amountPaid > 0) {
                SupplierPayment::query()->create([
                    'supplier_id' => $purchase->supplier_id,
                    'purchase_id' => $purchase->id,
                    'amount' => $amountPaid,
                    'method' => 'cash',
                    'reference' => $purchase->purchase_no,
                    'paid_at' => now(),
                    'created_by' => $actor->id,
                ]);
            }

            return $purchase->fresh(['items.variant.product', 'items.unit', 'supplier', 'creator']);
        });
    }

    private function applyStockIn(Purchase $purchase, PurchaseItem $item, User $actor): void
    {
        $qtyBase = (float) $item->quantity_base;

        $balance = StockBalance::query()->firstOrCreate(
            [
                'shop_id' => $purchase->shop_id,
                'variant_id' => $item->variant_id,
            ],
            ['qty_on_hand' => 0]
        );

        $balance = StockBalance::query()
            ->whereKey($balance->id)
            ->lockForUpdate()
            ->firstOrFail();

        $balance->qty_on_hand = round((float) $balance->qty_on_hand + $qtyBase, 3);
        $balance->save();

        $lotId = null;
        if ($item->lot_code || $item->expiry_date) {
            $lot = StockLot::query()->create([
                'variant_id' => $item->variant_id,
                'lot_code' => $item->lot_code,
                'expiry_date' => $item->expiry_date,
                'qty_on_hand' => $qtyBase,
            ]);
            $lotId = $lot->id;
        }

        StockMovement::query()->create([
            'shop_id' => $purchase->shop_id,
            'variant_id' => $item->variant_id,
            'lot_id' => $lotId,
            'movement_type' => 'purchase_in',
            'qty_delta' => $qtyBase,
            'qty_after' => $balance->qty_on_hand,
            'unit_cost' => $item->unit_cost,
            'reference_type' => Purchase::class,
            'reference_id' => $purchase->id,
            'reason' => 'Purchase receive '.$purchase->purchase_no,
            'occurred_at' => now(),
            'created_by' => $actor->id,
        ]);
    }

    private function recordPriceHistory(Purchase $purchase, PurchaseItem $item): void
    {
        SupplierPriceHistory::query()->create([
            'supplier_id' => $purchase->supplier_id,
            'variant_id' => $item->variant_id,
            'unit_id' => $item->unit_id,
            'unit_cost' => $item->unit_cost,
            'purchase_item_id' => $item->id,
            'effective_at' => now(),
        ]);
    }
}
