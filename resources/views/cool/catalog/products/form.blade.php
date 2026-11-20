@extends('cool.layouts.app')

@php
    $isEdit = isset($product) && $product;
    $variant = $variant ?? ($product?->variants?->first());
    $existingPrices = $existingPrices ?? collect();
@endphp

@section('title', $isEdit ? 'Edit Product' : 'Add Product')
@section('heading', $isEdit ? 'Edit Product' : 'Add Product')

@section('content')
    <form method="POST"
          action="{{ $isEdit ? route('cool.products.update', $product->id) : route('cool.products.store') }}"
          enctype="multipart/form-data"
          class="cs-panel p-5 space-y-6 max-w-5xl"
          x-data="productForm(@js($units), @js($existingPrices), '{{ $product->base_unit_id ?? ($units->firstWhere('abbreviation', 'Pcs')->id ?? $units->first()?->id) }}')">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2 flex gap-4 items-start">
                <img src="{{ $isEdit ? $product->imageUrl() : asset('images/cool/product-default.svg') }}"
                     alt="Product"
                     class="h-24 w-24 rounded-2xl object-cover border border-[var(--cs-line)] bg-slate-50">
                <div class="flex-1">
                    <label class="text-sm font-semibold">Product Image</label>
                    <input class="cs-input mt-1" type="file" name="image" accept="image/*">
                    <p class="mt-1 text-xs text-[var(--cs-muted)]">Optional. Default image shows when empty.</p>
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="text-sm font-semibold">Product Name</label>
                <input class="cs-input mt-1" name="name" value="{{ old('name', $product->name ?? '') }}" required>
            </div>
            <div>
                <label class="text-sm font-semibold">Category</label>
                <select class="cs-input mt-1" name="category_id" required>
                    <option value="">Select</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold">Brand</label>
                <select class="cs-input mt-1" name="brand_id">
                    <option value="">Optional</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id ?? '') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold">Base Unit</label>
                <select class="cs-input mt-1" name="base_unit_id" x-model="baseUnitId" required>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold">Type</label>
                <select class="cs-input mt-1" name="product_type">
                    <option value="standard" @selected(old('product_type', $product->product_type ?? 'standard') === 'standard')>Standard</option>
                    <option value="bundle" @selected(old('product_type', $product->product_type ?? '') === 'bundle')>Bundle</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="text-sm font-semibold">Description</label>
                <textarea class="cs-input mt-1" name="description" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
            </div>
            <div class="sm:col-span-2 flex flex-wrap gap-4 text-sm">
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="track_stock" value="1" @checked(old('track_stock', $product->track_stock ?? true))> Track stock</label>
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="track_expiry" value="1" @checked(old('track_expiry', $product->track_expiry ?? false))> Track expiry</label>
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))> Active</label>
            </div>
        </div>

        <div class="border-t border-[var(--cs-line)] pt-5 space-y-4">
            <h2 class="brand-font text-lg font-bold">Variant / SKU</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold">Variant Name</label>
                    <input class="cs-input mt-1" name="variant_name" value="{{ old('variant_name', $variant->name ?? 'Default') }}" required>
                </div>
                <div>
                    <label class="text-sm font-semibold">SKU</label>
                    <input class="cs-input mt-1" name="sku" value="{{ old('sku', $variant->sku ?? $suggestedSku) }}" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold">Barcode</label>
                    <div class="mt-1 flex gap-2">
                        <input class="cs-input" name="barcode" x-model="barcode" placeholder="Manual or generate">
                        <button type="button" class="cs-btn-ghost whitespace-nowrap" @click="generateBarcode()">Generate</button>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-semibold">Retail fallback</label>
                    <input class="cs-input mt-1" type="number" step="0.01" name="retail_price" value="{{ old('retail_price', $variant->retail_price ?? '') }}" required>
                </div>
                <div>
                    <label class="text-sm font-semibold">Wholesale fallback</label>
                    <input class="cs-input mt-1" type="number" step="0.01" name="wholesale_price" value="{{ old('wholesale_price', $variant->wholesale_price ?? '') }}" required>
                </div>
                <div>
                    <label class="text-sm font-semibold">Min wholesale qty</label>
                    <input class="cs-input mt-1" type="number" step="0.001" name="min_wholesale_qty" value="{{ old('min_wholesale_qty', $variant->min_wholesale_qty ?? '') }}">
                </div>
                @if($canViewCost)
                    <div>
                        <label class="text-sm font-semibold">Cost price (အရင်း)</label>
                        <input class="cs-input mt-1" type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $variant->cost_price ?? '') }}" required>
                    </div>
                @endif
                <div>
                    <label class="text-sm font-semibold">Reorder level</label>
                    <input class="cs-input mt-1" type="number" step="0.001" name="reorder_level" value="{{ old('reorder_level', $variant->reorder_level ?? 5) }}">
                </div>
                @unless($isEdit)
                    <div>
                        <label class="text-sm font-semibold">Opening qty (base)</label>
                        <input class="cs-input mt-1" type="number" step="0.001" name="opening_qty" value="{{ old('opening_qty', 0) }}">
                    </div>
                @endunless
            </div>
        </div>

        <div class="border-t border-[var(--cs-line)] pt-5 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="brand-font text-lg font-bold">Unit sell prices</h2>
                    <p class="text-sm text-[var(--cs-muted)]">Box ပုံမှန်ရောင်း / Pcs ခွဲရောင်း — တစ်ခုချင်း active status နဲ့ထိန်းပါ။</p>
                </div>
                <button type="button" class="cs-btn-ghost" @click="addRow()">Add unit price</button>
            </div>

            <template x-for="(row, index) in rows" :key="index">
                <div class="grid gap-3 sm:grid-cols-4 border border-[var(--cs-line)] rounded-2xl p-3">
                    <div>
                        <label class="text-xs font-semibold text-[var(--cs-muted)]">Unit</label>
                        <select class="cs-input mt-1" :name="`unit_prices[${index}][unit_id]`" x-model="row.unit_id" required>
                            <template x-for="unit in units" :key="unit.id">
                                <option :value="unit.id" x-text="`${unit.name} (${unit.abbreviation})`"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-[var(--cs-muted)]">Sell price</label>
                        <input class="cs-input mt-1" type="number" step="0.01" min="0" :name="`unit_prices[${index}][price]`" x-model="row.price" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-[var(--cs-muted)]">Factor to base</label>
                        <input class="cs-input mt-1" type="number" step="0.000001" min="0.000001" :name="`unit_prices[${index}][factor_to_base]`" x-model="row.factor_to_base" required>
                    </div>
                    <div class="flex items-end justify-between gap-2">
                        <label class="inline-flex items-center gap-2 text-sm mb-2">
                            <input type="checkbox" :name="`unit_prices[${index}][is_active]`" value="1" x-model="row.is_active"> Active
                        </label>
                        <button type="button" class="text-sm text-rose-600 mb-2" @click="removeRow(index)" x-show="rows.length > 1">Remove</button>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex gap-3">
            <button class="cs-btn-primary" type="submit">{{ $isEdit ? 'Update Product' : 'Save Product' }}</button>
            <a href="{{ route('cool.products.index') }}" class="cs-btn-ghost">Cancel</a>
        </div>
    </form>

    <script>
        function productForm(units, existingPrices, baseUnitId) {
            const mapped = (existingPrices || []).map((price) => ({
                unit_id: price.unit_id,
                price: Number(price.price),
                factor_to_base: Number(price.factor_to_base || 1),
                is_active: !!price.is_active,
            }));

            return {
                units,
                baseUnitId,
                barcode: @json(old('barcode', $variant->barcode ?? $suggestedBarcode)),
                rows: mapped.length ? mapped : [{
                    unit_id: baseUnitId,
                    price: '',
                    factor_to_base: 1,
                    is_active: true,
                }],
                addRow() {
                    this.rows.push({
                        unit_id: this.units[0]?.id,
                        price: '',
                        factor_to_base: 1,
                        is_active: true,
                    });
                },
                removeRow(index) {
                    if (this.rows.length > 1) this.rows.splice(index, 1);
                },
                async generateBarcode() {
                    const response = await fetch(@json(route('cool.products.barcode')));
                    const data = await response.json();
                    this.barcode = data.barcode;
                },
            }
        }
    </script>
@endsection
