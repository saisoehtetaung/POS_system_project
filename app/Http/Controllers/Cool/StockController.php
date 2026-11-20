<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Inventory\Models\StockBalance;
use App\Domain\Inventory\Models\StockLot;
use App\Domain\Inventory\Models\StockMovement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $shopId = auth()->user()->shop_id;
        $tab = $request->get('tab', 'all');

        $balances = StockBalance::query()
            ->with('variant.product')
            ->where('shop_id', $shopId)
            ->orderByDesc('updated_at')
            ->paginate(20, ['*'], 'all_page')
            ->withQueryString();

        $low = StockBalance::query()
            ->with('variant.product')
            ->where('shop_id', $shopId)
            ->whereColumn(
                'qty_on_hand',
                '<=',
                DB::raw('(select reorder_level from product_variants where product_variants.id = stock_balances.variant_id)')
            )
            ->paginate(20, ['*'], 'low_page')
            ->withQueryString();

        $expired = StockLot::query()
            ->with('variant.product')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->toDateString())
            ->where('qty_on_hand', '>', 0)
            ->whereHas('variant.product', fn ($q) => $q->where('shop_id', $shopId))
            ->orderBy('expiry_date')
            ->paginate(20, ['*'], 'expired_page')
            ->withQueryString();

        $variants = collect();
        if (auth()->user()->hasRole('owner', 'manager')) {
            $variants = ProductVariant::query()
                ->with('product')
                ->whereHas('product', fn ($q) => $q->where('shop_id', $shopId))
                ->orderBy('name')
                ->get();
        }

        return view('cool.stock.index', compact('tab', 'balances', 'low', 'expired', 'variants'));
    }

    public function low()
    {
        return redirect()->route('cool.stock.index', ['tab' => 'low']);
    }

    public function expired()
    {
        return redirect()->route('cool.stock.index', ['tab' => 'expired']);
    }

    public function adjust(Request $request)
    {
        if (! auth()->user()->hasRole('owner', 'manager')) {
            abort(403);
        }

        $shopId = auth()->user()->shop_id;

        $data = $request->validate([
            'variant_id' => [
                'required',
                Rule::exists('product_variants', 'id')->where(function ($query) use ($shopId) {
                    $query->whereIn('product_id', function ($sub) use ($shopId) {
                        $sub->select('id')->from('products')->where('shop_id', $shopId);
                    });
                }),
            ],
            'movement_type' => ['required', Rule::in(['damage', 'loss', 'audit_adjustment'])],
            'qty_delta' => ['required', 'numeric'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data, $shopId) {
            $balance = StockBalance::query()->firstOrCreate(
                ['shop_id' => $shopId, 'variant_id' => $data['variant_id']],
                ['qty_on_hand' => 0]
            );

            $delta = (float) $data['qty_delta'];
            if (in_array($data['movement_type'], ['damage', 'loss'], true) && $delta > 0) {
                $delta = -$delta;
            }

            $balance->qty_on_hand = $balance->qty_on_hand + $delta;
            $balance->save();

            StockMovement::query()->create([
                'shop_id' => $shopId,
                'variant_id' => $data['variant_id'],
                'movement_type' => $data['movement_type'],
                'qty_delta' => $delta,
                'qty_after' => $balance->qty_on_hand,
                'reason' => $data['reason'],
                'occurred_at' => now(),
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('cool.stock.index', ['tab' => 'all'])
            ->with('success', 'Stock adjustment saved.');
    }
}
