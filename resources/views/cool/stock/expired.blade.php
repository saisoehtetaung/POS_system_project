@extends('cool.layouts.app')

@section('title', 'Expired Products')
@section('heading', 'Expired Products')

@section('content')
    <section class="cs-panel overflow-hidden">
        <table class="cs-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Lot</th>
                    <th>Expiry</th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lots as $lot)
                    <tr>
                        <td class="font-semibold">{{ optional($lot->variant->product)->name }}</td>
                        <td>{{ $lot->lot_code ?? '—' }}</td>
                        <td>{{ optional($lot->expiry_date)->format('d M Y') }}</td>
                        <td>{{ number_format($lot->qty_on_hand, 3) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-8 text-center text-[var(--cs-muted)]">No expired lots.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $lots->links() }}</div>
    </section>
@endsection
