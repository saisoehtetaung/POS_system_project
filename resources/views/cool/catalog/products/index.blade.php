@extends('cool.layouts.app')

@section('title', 'Products')
@section('heading', 'Products')

@section('actions')
    @if(auth()->user()->hasRole('owner', 'manager'))
        <a href="{{ route('cool.products.create') }}" class="cs-btn-ghost">Add Product</a>
    @endif
@endsection

@section('content')
    <form method="GET" class="cs-panel p-4 flex flex-col gap-3 sm:flex-row">
        <input type="search" name="search" value="{{ request('search') }}" class="cs-input" placeholder="Search name, SKU, barcode">
        <button class="cs-btn-primary">Search</button>
    </form>

    <section class="cs-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="cs-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Unit prices</th>
                        <th>SKUs</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php $first = $product->variants->first(); @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <img src="{{ $product->imageUrl() }}" alt="" class="h-12 w-12 rounded-xl object-cover border border-[var(--cs-line)]">
                                    <div>
                                        <div class="font-semibold">{{ $product->name }}</div>
                                        <div class="text-xs text-[var(--cs-muted)]">{{ optional($product->brand)->name }} · {{ optional($first)->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ optional($product->category)->name }}</td>
                            <td>
                                @forelse (optional($first)?->unitPrices ?? [] as $price)
                                    <div class="text-xs">
                                        {{ optional($price->unit)->abbreviation }}:
                                        {{ number_format($price->price, 0) }}
                                        @unless($price->is_active)<span class="text-rose-600">(off)</span>@endunless
                                    </div>
                                @empty
                                    {{ $first ? number_format($first->retail_price, 0) : '—' }}
                                @endforelse
                            </td>
                            <td>{{ $product->variants->count() }}</td>
                            <td>
                                @if ($product->is_active)
                                    <span class="cs-badge-ok">Active</span>
                                @else
                                    <span class="cs-badge-warn">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                @if(auth()->user()->hasRole('owner', 'manager'))
                                    <a href="{{ route('cool.products.edit', $product->id) }}" class="text-sm font-semibold text-[var(--cs-accent-deep)]">Edit</a>
                                    <form action="{{ route('cool.products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm font-semibold text-rose-600 ml-3" type="submit">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-[var(--cs-muted)]">No products yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $products->links() }}</div>
    </section>
@endsection
