@extends('cool.layouts.app')

@section('title', 'Add Product')
@section('heading', 'Add Product')

@section('content')
    <form method="POST" action="{{ route('cool.products.store') }}" class="cs-panel p-5 space-y-5 max-w-4xl">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="text-sm font-semibold">Product Name</label>
                <input class="cs-input mt-1" name="name" value="{{ old('name') }}" required>
            </div>
            <div>
                <label class="text-sm font-semibold">Category</label>
                <select class="cs-input mt-1" name="category_id" required>
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold">Brand</label>
                <select class="cs-input mt-1" name="brand_id">
                    <option value="">Optional</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold">Base Unit</label>
                <select class="cs-input mt-1" name="base_unit_id" required>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(old('base_unit_id', optional($units->firstWhere('abbreviation', 'Pcs'))->id) == $unit->id)>
                            {{ $unit->name }} ({{ $unit->abbreviation }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold">Type</label>
                <select class="cs-input mt-1" name="product_type">
                    <option value="standard">Standard</option>
                    <option value="bundle">Bundle</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="text-sm font-semibold">Description</label>
                <textarea class="cs-input mt-1" name="description" rows="3">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="border-t border-[var(--cs-line)] pt-5">
            <h2 class="brand-font text-lg font-bold mb-4">Default Variant / SKU</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold">Variant Name</label>
                    <input class="cs-input mt-1" name="variant_name" value="{{ old('variant_name', 'Default') }}" required>
                </div>
                <div>
                    <label class="text-sm font-semibold">SKU</label>
                    <input class="cs-input mt-1" name="sku" value="{{ old('sku', $suggestedSku) }}" required>
                </div>
                <div>
                    <label class="text-sm font-semibold">Barcode</label>
                    <input class="cs-input mt-1" name="barcode" value="{{ old('barcode') }}">
                </div>
                <div>
                    <label class="text-sm font-semibold">Opening Qty (Pcs)</label>
                    <input class="cs-input mt-1" type="number" step="0.001" name="opening_qty" value="{{ old('opening_qty', 0) }}">
                </div>
                <div>
                    <label class="text-sm font-semibold">Retail Price</label>
                    <input class="cs-input mt-1" type="number" step="0.01" name="retail_price" value="{{ old('retail_price') }}" required>
                </div>
                <div>
                    <label class="text-sm font-semibold">Wholesale Price</label>
                    <input class="cs-input mt-1" type="number" step="0.01" name="wholesale_price" value="{{ old('wholesale_price') }}" required>
                </div>
                <div>
                    <label class="text-sm font-semibold">Min Wholesale Qty</label>
                    <input class="cs-input mt-1" type="number" step="0.001" name="min_wholesale_qty" value="{{ old('min_wholesale_qty') }}">
                </div>
                <div>
                    <label class="text-sm font-semibold">Cost Price</label>
                    <input class="cs-input mt-1" type="number" step="0.01" name="cost_price" value="{{ old('cost_price') }}" required>
                </div>
                <div>
                    <label class="text-sm font-semibold">Reorder Level</label>
                    <input class="cs-input mt-1" type="number" step="0.001" name="reorder_level" value="{{ old('reorder_level', 5) }}">
                </div>
            </div>
            <div class="mt-4 flex gap-4 text-sm">
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="track_stock" value="1" checked> Track stock</label>
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="track_expiry" value="1"> Track expiry</label>
            </div>
        </div>

        <div class="flex gap-3">
            <button class="cs-btn-primary" type="submit">Save Product</button>
            <a href="{{ route('cool.products.index') }}" class="cs-btn-ghost">Cancel</a>
        </div>
    </form>
@endsection
