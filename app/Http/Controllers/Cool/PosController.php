<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\UnitConversion;
use App\Domain\Customers\Models\Customer;
use App\Domain\Identity\Models\Shop;
use App\Domain\Inventory\Models\StockBalance;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Services\CheckoutService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $shopId = $user->shop_id;
        $shop = Shop::query()->whereKey($shopId)->firstOrFail();

        $categories = Category::query()
            ->where('shop_id', $shopId)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $variants = ProductVariant::query()
            ->with([
                'product.category',
                'product.baseUnit',
                'activeUnitPrices.unit',
            ])
            ->where('is_active', true)
            ->whereHas('product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId)->where('is_active', true);
            })
            ->orderBy('name')
            ->get();

        $balances = StockBalance::query()
            ->where('shop_id', $shopId)
            ->whereIn('variant_id', $variants->pluck('id'))
            ->pluck('qty_on_hand', 'variant_id');

        $conversions = UnitConversion::query()
            ->whereIn('product_id', $variants->pluck('product_id')->unique())
            ->get()
            ->groupBy('product_id');

        $payload = $variants->map(function (ProductVariant $variant) use ($conversions, $balances) {
            $productConversions = $conversions->get($variant->product_id, collect());
            $unitPrices = $variant->activeUnitPrices->map(function ($price) use ($productConversions) {
                $factor = optional($productConversions->firstWhere('unit_id', $price->unit_id))->factor_to_base ?? 1;

                return [
                    'unit_id' => $price->unit_id,
                    'unit_name' => optional($price->unit)->abbreviation ?? 'Unit',
                    'price' => (float) $price->price,
                    'factor_to_base' => (float) $factor,
                ];
            })->values();

            if ($unitPrices->isEmpty()) {
                $unitPrices = collect([[
                    'unit_id' => optional($variant->product->baseUnit)->id,
                    'unit_name' => optional($variant->product->baseUnit)->abbreviation ?? 'Pcs',
                    'price' => (float) $variant->retail_price,
                    'factor_to_base' => 1,
                ]]);
            }

            return [
                'id' => $variant->id,
                'product_id' => $variant->product_id,
                'product_name' => $variant->product->name,
                'category_id' => $variant->product->category_id,
                'image_url' => $variant->product->imageUrl(),
                'name' => $variant->name,
                'sku' => $variant->sku,
                'barcode' => $variant->barcode,
                'retail_price' => (float) $variant->retail_price,
                'wholesale_price' => (float) $variant->wholesale_price,
                'min_wholesale_qty' => $variant->min_wholesale_qty ? (float) $variant->min_wholesale_qty : null,
                'unit_prices' => $unitPrices,
                'track_stock' => (bool) $variant->product->track_stock,
                'qty_on_hand' => (float) ($balances[$variant->id] ?? 0),
            ];
        });

        $customers = Customer::query()
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'balance', 'credit_limit'])
            ->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'balance' => (float) $customer->balance,
                'credit_limit' => (float) $customer->credit_limit,
                'available_credit' => max(0, (float) $customer->credit_limit - (float) $customer->balance),
            ]);

        return view('cool.pos.index', [
            'categories' => $categories,
            'variants' => $payload,
            'customers' => $customers,
            'shop' => [
                'tax_rate' => (float) $shop->tax_rate,
                'currency_code' => $shop->currency_code ?: 'MMK',
                'receipt_header' => $shop->receipt_header,
                'receipt_footer' => $shop->receipt_footer,
            ],
            'cashier' => [
                'id' => $user->id,
                'name' => $user->name,
                'login_id' => $user->login_id,
                'role' => $user->roleName(),
            ],
            'checkoutUrl' => route('cool.pos.checkout'),
        ]);
    }

    public function checkout(Request $request, CheckoutService $checkout)
    {
        $data = $request->validate([
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.variant_id' => ['required', 'string'],
            'cart.*.unit_id' => ['nullable', 'string'],
            'cart.*.unit_name' => ['nullable', 'string'],
            'cart.*.qty' => ['required', 'numeric', 'min:0.001'],
            'cart.*.unit_price' => ['required', 'numeric', 'min:0'],
            'cart.*.factor_to_base' => ['required', 'numeric', 'min:0.000001'],
            'customer_id' => ['nullable', 'string'],
            'use_credit' => ['nullable', 'boolean'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $sale = $checkout->checkout(
                auth()->user(),
                $data['cart'],
                $data['customer_id'] ?? null,
                (bool) ($data['use_credit'] ?? false),
                (float) ($data['amount_paid'] ?? 0)
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first() ?: 'Checkout failed.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'Sale completed.',
            'sale_id' => $sale->id,
            'sale_no' => $sale->sale_no,
            'receipt_url' => route('cool.pos.receipt', $sale->id),
        ]);
    }

    public function receipt(string $id)
    {
        $sale = Sale::query()
            ->with(['items', 'cashier', 'customer', 'shop', 'payments'])
            ->where('shop_id', auth()->user()->shop_id)
            ->whereKey($id)
            ->firstOrFail();

        return view('cool.pos.receipt', compact('sale'));
    }
}
