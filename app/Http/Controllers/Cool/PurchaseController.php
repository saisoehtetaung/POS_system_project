<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Catalog\Models\UnitConversion;
use App\Domain\Purchasing\Models\Purchase;
use App\Domain\Purchasing\Models\PurchaseItem;
use App\Domain\Purchasing\Models\Supplier;
use App\Domain\Purchasing\Services\ReceivePurchaseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $purchases = Purchase::query()
            ->with(['supplier', 'creator'])
            ->where('shop_id', auth()->user()->shop_id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('purchased_at')
            ->paginate(20)
            ->withQueryString();

        return view('cool.purchases.index', compact('purchases', 'status'));
    }

    public function create()
    {
        return view('cool.purchases.form', $this->formData());
    }

    public function store(Request $request)
    {
        $purchase = $this->saveDraft($request);

        return redirect()
            ->route('cool.purchases.show', $purchase->id)
            ->with('success', 'Draft purchase created.');
    }

    public function show(string $id)
    {
        $purchase = $this->shopPurchase($id)->load([
            'supplier',
            'creator',
            'items.variant.product',
            'items.unit',
            'payments',
        ]);

        return view('cool.purchases.show', compact('purchase'));
    }

    public function edit(string $id)
    {
        $purchase = $this->shopPurchase($id);

        if ($purchase->status !== 'draft') {
            return redirect()
                ->route('cool.purchases.show', $purchase->id)
                ->withErrors(['purchase' => 'Only draft purchases can be edited.']);
        }

        $purchase->load(['items.variant.product', 'items.unit']);

        return view('cool.purchases.form', array_merge($this->formData(), [
            'purchase' => $purchase,
        ]));
    }

    public function update(Request $request, string $id)
    {
        $purchase = $this->shopPurchase($id);

        if ($purchase->status !== 'draft') {
            return redirect()
                ->route('cool.purchases.show', $purchase->id)
                ->withErrors(['purchase' => 'Only draft purchases can be edited.']);
        }

        $purchase = $this->saveDraft($request, $purchase);

        return redirect()
            ->route('cool.purchases.show', $purchase->id)
            ->with('success', 'Draft purchase updated.');
    }

    public function receive(string $id, ReceivePurchaseService $receiver)
    {
        $purchase = $this->shopPurchase($id);

        try {
            $receiver->receive($purchase, auth()->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()
            ->route('cool.purchases.show', $purchase->id)
            ->with('success', 'Purchase received. Stock updated.');
    }

    public function cancel(string $id)
    {
        $purchase = $this->shopPurchase($id);

        if ($purchase->status !== 'draft') {
            return back()->withErrors(['purchase' => 'Only draft purchases can be cancelled.']);
        }

        $purchase->update(['status' => 'cancelled']);

        return redirect()
            ->route('cool.purchases.show', $purchase->id)
            ->with('success', 'Draft purchase cancelled.');
    }

    private function formData(): array
    {
        $shopId = auth()->user()->shop_id;

        $suppliers = Supplier::query()
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $variants = ProductVariant::query()
            ->with(['product.baseUnit', 'activeUnitPrices.unit'])
            ->where('is_active', true)
            ->whereHas('product', fn ($query) => $query->where('shop_id', $shopId)->where('is_active', true))
            ->orderBy('name')
            ->get();

        $conversions = UnitConversion::query()
            ->whereIn('product_id', $variants->pluck('product_id')->unique())
            ->get()
            ->groupBy('product_id');

        $variantPayload = $variants->map(function (ProductVariant $variant) use ($conversions) {
            $productConversions = $conversions->get($variant->product_id, collect());
            $units = collect();

            if ($variant->product->baseUnit) {
                $units->push([
                    'unit_id' => $variant->product->base_unit_id,
                    'unit_name' => $variant->product->baseUnit->abbreviation,
                    'factor_to_base' => 1,
                ]);
            }

            foreach ($variant->activeUnitPrices as $price) {
                $factor = optional($productConversions->firstWhere('unit_id', $price->unit_id))->factor_to_base ?? 1;
                if (! $units->contains(fn ($unit) => $unit['unit_id'] === $price->unit_id)) {
                    $units->push([
                        'unit_id' => $price->unit_id,
                        'unit_name' => optional($price->unit)->abbreviation ?? 'Unit',
                        'factor_to_base' => (float) $factor,
                    ]);
                }
            }

            foreach ($productConversions as $conversion) {
                if (! $units->contains(fn ($unit) => $unit['unit_id'] === $conversion->unit_id)) {
                    $units->push([
                        'unit_id' => $conversion->unit_id,
                        'unit_name' => optional(Unit::query()->find($conversion->unit_id))->abbreviation ?? 'Unit',
                        'factor_to_base' => (float) $conversion->factor_to_base,
                    ]);
                }
            }

            return [
                'id' => $variant->id,
                'label' => $variant->product->name.' · '.$variant->name.' ('.$variant->sku.')',
                'product_id' => $variant->product_id,
                'units' => $units->values(),
            ];
        });

        return [
            'purchase' => null,
            'suppliers' => $suppliers,
            'variants' => $variantPayload,
        ];
    }

    private function saveDraft(Request $request, ?Purchase $purchase = null): Purchase
    {
        $shopId = auth()->user()->shop_id;

        $data = $request->validate([
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->where(fn ($query) => $query->where('shop_id', $shopId)->whereNull('deleted_at')),
            ],
            'payment_type' => ['required', Rule::in(['cash', 'credit'])],
            'due_date' => ['nullable', 'date', 'required_if:payment_type,credit'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => [
                'required',
                Rule::exists('product_variants', 'id')->where(function ($query) use ($shopId) {
                    $query->whereIn('product_id', function ($sub) use ($shopId) {
                        $sub->select('id')->from('products')->where('shop_id', $shopId);
                    });
                }),
            ],
            'items.*.unit_id' => ['required', Rule::exists('units', 'id')->where('shop_id', $shopId)],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.lot_code' => ['nullable', 'string', 'max:100'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ]);

        return DB::transaction(function () use ($data, $purchase, $shopId) {
            $lines = [];
            $subtotal = 0;

            foreach ($data['items'] as $row) {
                $variant = ProductVariant::query()
                    ->with('product')
                    ->whereKey($row['variant_id'])
                    ->firstOrFail();

                $factor = UnitConversion::query()
                    ->where('product_id', $variant->product_id)
                    ->where('unit_id', $row['unit_id'])
                    ->value('factor_to_base');

                if ($factor === null) {
                    $factor = $variant->product->base_unit_id === $row['unit_id'] ? 1 : null;
                }

                if ($factor === null) {
                    throw ValidationException::withMessages([
                        'items' => 'Missing unit conversion for one of the selected units.',
                    ]);
                }

                $qty = (float) $row['quantity'];
                $cost = (float) $row['unit_cost'];
                $lineTotal = round($qty * $cost, 2);
                $subtotal += $lineTotal;

                $lines[] = [
                    'variant_id' => $variant->id,
                    'unit_id' => $row['unit_id'],
                    'quantity' => $qty,
                    'factor_to_base' => (float) $factor,
                    'quantity_base' => round($qty * (float) $factor, 3),
                    'unit_cost' => $cost,
                    'line_total' => $lineTotal,
                    'lot_code' => $row['lot_code'] ?? null,
                    'expiry_date' => $row['expiry_date'] ?? null,
                ];
            }

            $payload = [
                'shop_id' => $shopId,
                'supplier_id' => $data['supplier_id'],
                'status' => 'draft',
                'payment_type' => $data['payment_type'],
                'purchased_at' => now(),
                'due_date' => $data['payment_type'] === 'credit' ? ($data['due_date'] ?? null) : null,
                'subtotal' => round($subtotal, 2),
                'discount_amount' => 0,
                'total' => round($subtotal, 2),
                'amount_paid' => 0,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ];

            if ($purchase) {
                $purchase->update($payload);
                $purchase->items()->delete();
            } else {
                $payload['purchase_no'] = $this->nextPurchaseNo();
                $purchase = Purchase::query()->create($payload);
            }

            foreach ($lines as $line) {
                PurchaseItem::query()->create(array_merge($line, [
                    'purchase_id' => $purchase->id,
                ]));
            }

            return $purchase->fresh(['items', 'supplier']);
        });
    }

    private function nextPurchaseNo(): string
    {
        $prefix = 'PO-'.now()->format('Ymd').'-';

        $latest = Purchase::query()
            ->where('purchase_no', 'like', $prefix.'%')
            ->orderByDesc('purchase_no')
            ->value('purchase_no');

        $sequence = 1;
        if (is_string($latest) && preg_match('/-(\d+)$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function shopPurchase(string $id): Purchase
    {
        return Purchase::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->whereKey($id)
            ->firstOrFail();
    }
}
