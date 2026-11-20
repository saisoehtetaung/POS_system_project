@extends('cool.layouts.app')

@section('title', 'Low Stock')
@section('heading', 'Low Stock Alerts')

@section('content')
    <section class="cs-panel overflow-hidden">
        <table class="cs-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Qty</th>
                    <th>Reorder Level</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($balances as $balance)
                    <tr>
                        <td class="font-semibold">{{ optional($balance->variant->product)->name }}</td>
                        <td>{{ optional($balance->variant)->sku }}</td>
                        <td><span class="cs-badge-warn">{{ number_format($balance->qty_on_hand, 3) }}</span></td>
                        <td>{{ number_format(optional($balance->variant)->reorder_level, 3) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-8 text-center text-[var(--cs-muted)]">Stock levels look healthy.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $balances->links() }}</div>
    </section>
@endsection
