<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\ProductVariantUnitPrice;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Catalog\Models\UnitConversion;
use App\Domain\Catalog\Services\BarcodeGenerator;
use App\Domain\Inventory\Models\StockBalance;
use App\Domain\Sales\Models\SaleItem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $shopId = auth()->user()->shop_id;

        $products = Product::query()
            ->with(['category', 'brand', 'baseUnit', 'variants.unitPrices.unit'])
            ->where('shop_id', $shopId)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhereHas('variants', function ($variants) use ($search) {
                            $variants->where('sku', 'like', "%{$search}%")
                                ->orWhere('barcode', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('cool.catalog.products.index', compact('products'));
    }

    public function create(BarcodeGenerator $barcodes)
    {
        return view('cool.catalog.products.form', array_merge($this->formData(), [
            'product' => null,
            'suggestedSku' => 'SKU-'.Str::upper(Str::random(6)),
            'suggestedBarcode' => $barcodes->generate(),
        ]));
    }

    public function store(Request $request)
    {
        $shopId = auth()->user()->shop_id;
        $data = $this->validated($request, $shopId);

        if (! auth()->user()->canViewCost()) {
            abort(403);
        }

        DB::transaction(function () use ($request, $data, $shopId) {
            $product = Product::query()->create([
                'shop_id' => $shopId,
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'base_unit_id' => $data['base_unit_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'image_path' => $this->storeImage($request),
                'product_type' => $data['product_type'],
                'track_stock' => (bool) ($data['track_stock'] ?? false),
                'track_expiry' => (bool) ($data['track_expiry'] ?? false),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncBaseConversion($product);
            $variant = $this->upsertPrimaryVariant($product, $data);
            $this->syncUnitPrices($product, $variant, $data['unit_prices'] ?? []);

            StockBalance::query()->firstOrCreate(
                ['shop_id' => $shopId, 'variant_id' => $variant->id],
                ['qty_on_hand' => (float) ($data['opening_qty'] ?? 0)]
            );
        });

        return redirect()
            ->route('cool.products.index')
            ->with('success', 'Product created.');
    }

    public function edit(string $id, BarcodeGenerator $barcodes)
    {
        $product = $this->shopProduct($id)->load(['variants.unitPrices.unit', 'unitConversions']);
        $variant = $product->variants->first();
        if ($variant && auth()->user()->canViewCost()) {
            $variant->makeVisible(['cost_price']);
        }
        $factors = $product->unitConversions->pluck('factor_to_base', 'unit_id');
        $existingPrices = ($variant?->unitPrices ?? collect())->map(function ($price) use ($factors) {
            return [
                'unit_id' => $price->unit_id,
                'price' => (float) $price->price,
                'factor_to_base' => (float) ($factors[$price->unit_id] ?? 1),
                'is_active' => (bool) $price->is_active,
            ];
        })->values();

        return view('cool.catalog.products.form', array_merge($this->formData(), [
            'product' => $product,
            'variant' => $variant,
            'existingPrices' => $existingPrices,
            'suggestedSku' => $variant?->sku ?? 'SKU-'.Str::upper(Str::random(6)),
            'suggestedBarcode' => $variant?->barcode ?? $barcodes->generate(),
        ]));
    }

    public function update(Request $request, string $id)
    {
        $shopId = auth()->user()->shop_id;
        $product = $this->shopProduct($id);
        $variant = $product->variants()->first();
        $data = $this->validated($request, $shopId, $variant?->id);

        if (! auth()->user()->canViewCost()) {
            unset($data['cost_price']);
        }

        DB::transaction(function () use ($request, $data, $product, $shopId) {
            $payload = [
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'base_unit_id' => $data['base_unit_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'product_type' => $data['product_type'],
                'track_stock' => (bool) ($data['track_stock'] ?? false),
                'track_expiry' => (bool) ($data['track_expiry'] ?? false),
                'is_active' => (bool) ($data['is_active'] ?? false),
            ];

            if ($path = $this->storeImage($request, $product->image_path)) {
                $payload['image_path'] = $path;
            }

            $product->update($payload);
            $this->syncBaseConversion($product);
            $variant = $this->upsertPrimaryVariant($product, $data);
            $this->syncUnitPrices($product, $variant, $data['unit_prices'] ?? []);

            StockBalance::query()->firstOrCreate(
                ['shop_id' => $shopId, 'variant_id' => $variant->id],
                ['qty_on_hand' => 0]
            );
        });

        return redirect()
            ->route('cool.products.index')
            ->with('success', 'Product updated.');
    }

    public function destroy(string $id)
    {
        $product = $this->shopProduct($id);

        $inParked = SaleItem::query()
            ->where('product_id', $product->id)
            ->whereHas('sale', fn ($q) => $q->where('status', 'parked'))
            ->exists();

        if ($inParked) {
            return back()->withErrors(['product' => 'Cannot delete a product referenced by a parked bill.']);
        }

        DB::transaction(function () use ($product) {
            $product->variants()->each(function (ProductVariant $variant) {
                $variant->unitPrices()->delete();
                $variant->delete();
            });
            $product->delete();
        });

        return redirect()
            ->route('cool.products.index')
            ->with('success', 'Product deleted.');
    }

    public function generateBarcode(BarcodeGenerator $barcodes)
    {
        return response()->json(['barcode' => $barcodes->generate()]);
    }

    private function validated(Request $request, string $shopId, ?string $variantId = null): array
    {
        $barcodeRule = Rule::unique('product_variants', 'barcode')->whereNotNull('barcode');
        $skuRule = Rule::unique('product_variants', 'sku');

        if ($variantId) {
            $barcodeRule = $barcodeRule->ignore($variantId);
            $skuRule = $skuRule->ignore($variantId);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('shop_id', $shopId)],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->where('shop_id', $shopId)],
            'base_unit_id' => ['required', Rule::exists('units', 'id')->where('shop_id', $shopId)],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'product_type' => ['required', Rule::in(['standard', 'bundle'])],
            'track_stock' => ['nullable', 'boolean'],
            'track_expiry' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'variant_name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', $skuRule],
            'barcode' => ['nullable', 'string', 'max:100', $barcodeRule],
            'retail_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['required', 'numeric', 'min:0'],
            'min_wholesale_qty' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'opening_qty' => ['nullable', 'numeric', 'min:0'],
            'unit_prices' => ['required', 'array', 'min:1'],
            'unit_prices.*.unit_id' => ['required', Rule::exists('units', 'id')->where('shop_id', $shopId)],
            'unit_prices.*.price' => ['required', 'numeric', 'min:0'],
            'unit_prices.*.factor_to_base' => ['required', 'numeric', 'min:0.000001'],
            'unit_prices.*.is_active' => ['nullable', 'boolean'],
        ];

        if (auth()->user()->canViewCost()) {
            $rules['cost_price'] = ['required', 'numeric', 'min:0'];
        }

        $data = $request->validate($rules);

        $hasBase = collect($data['unit_prices'])->contains(function ($row) use ($data) {
            return ($row['unit_id'] ?? null) === $data['base_unit_id'];
        });

        if (! $hasBase) {
            throw ValidationException::withMessages([
                'unit_prices' => 'Include a sell price for the base unit (Pcs).',
            ]);
        }

        return $data;
    }

    private function shopProduct(string $id): Product
    {
        return Product::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->whereKey($id)
            ->firstOrFail();
    }

    private function formData(): array
    {
        $shopId = auth()->user()->shop_id;

        return [
            'categories' => Category::query()->where('shop_id', $shopId)->orderBy('name')->get(),
            'brands' => Brand::query()->where('shop_id', $shopId)->orderBy('name')->get(),
            'units' => Unit::query()->where('shop_id', $shopId)->orderBy('name')->get(),
            'canViewCost' => auth()->user()->canViewCost(),
        ];
    }

    private function storeImage(Request $request, ?string $existing = null): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        if ($existing) {
            Storage::disk('public')->delete($existing);
        }

        return $request->file('image')->store('products', 'public');
    }

    private function syncBaseConversion(Product $product): void
    {
        UnitConversion::query()->updateOrCreate(
            ['product_id' => $product->id, 'unit_id' => $product->base_unit_id],
            ['factor_to_base' => 1]
        );
    }

    private function upsertPrimaryVariant(Product $product, array $data): ProductVariant
    {
        $variant = $product->variants()->first();

        $payload = [
            'product_id' => $product->id,
            'sku' => $data['sku'],
            'barcode' => $data['barcode'] ?: null,
            'name' => $data['variant_name'],
            'retail_price' => $data['retail_price'],
            'wholesale_price' => $data['wholesale_price'],
            'min_wholesale_qty' => $data['min_wholesale_qty'] ?? null,
            'reorder_level' => $data['reorder_level'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];

        if (array_key_exists('cost_price', $data)) {
            $payload['cost_price'] = $data['cost_price'];
        } elseif (! $variant) {
            $payload['cost_price'] = 0;
        }

        if ($variant) {
            $variant->update($payload);

            return $variant->fresh();
        }

        return ProductVariant::query()->create($payload);
    }

    private function syncUnitPrices(Product $product, ProductVariant $variant, array $rows): void
    {
        $kept = [];

        foreach ($rows as $row) {
            if (empty($row['unit_id'])) {
                continue;
            }

            UnitConversion::query()->updateOrCreate(
                ['product_id' => $product->id, 'unit_id' => $row['unit_id']],
                ['factor_to_base' => $row['factor_to_base']]
            );

            $price = ProductVariantUnitPrice::query()->updateOrCreate(
                [
                    'variant_id' => $variant->id,
                    'unit_id' => $row['unit_id'],
                ],
                [
                    'price' => $row['price'],
                    'is_active' => (bool) ($row['is_active'] ?? false),
                ]
            );

            $kept[] = $price->id;
        }

        ProductVariantUnitPrice::query()
            ->where('variant_id', $variant->id)
            ->when($kept, fn ($q) => $q->whereNotIn('id', $kept), fn ($q) => $q)
            ->delete();
    }
}
