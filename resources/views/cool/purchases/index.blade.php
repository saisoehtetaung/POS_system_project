@extends('cool.layouts.app')

@section('title', 'Purchases')
@section('heading', 'Purchases')

@section('actions')
    <a href="{{ route('cool.purchases.create') }}" class="cs-btn-primary">New Purchase</a>
@endsection

@section('content')
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('cool.purchases.index') }}" class="cs-btn-ghost {{ ! $status ? 'ring-2 ring-[var(--cs-accent)]' : '' }}">All</a>
        @foreach (['draft', 'received', 'cancelled'] as $filter)
            <a href="{{ route('cool.purchases.index', ['status' => $filter]) }}" class="cs-btn-ghost {{ $status === $filter ? 'ring-2 ring-[var(--cs-accent)]' : '' }}">{{ ucfirst($filter) }}</a>
        @endforeach
    </div>

    <section class="cs-panel overflow-hidden">
        <table class="cs-table">
            <thead>
                <tr>
                    <th>Purchase no</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr>
                        <td class="font-semibold">{{ $purchase->purchase_no }}</td>
                        <td>{{ optional($purchase->supplier)->name }}</td>
                        <td>{{ optional($purchase->purchased_at)->format('d M Y') }}</td>
                        <td class="capitalize">{{ $purchase->payment_type }}</td>
                        <td>{{ number_format($purchase->total, 0) }}</td>
                        <td>
                            @if($purchase->status === 'draft')
                                <span class="cs-badge-soft">Draft</span>
                            @elseif($purchase->status === 'received')
                                <span class="cs-badge-ok">Received</span>
                            @else
                                <span class="cs-badge-warn">Cancelled</span>
                            @endif
                        </td>
                        <td class="text-right space-x-3">
                            <a href="{{ route('cool.purchases.show', $purchase->id) }}" class="text-sm font-semibold text-[var(--cs-accent-deep)]">View</a>
                            @if($purchase->status === 'draft')
                                <a href="{{ route('cool.purchases.edit', $purchase->id) }}" class="text-sm font-semibold text-[var(--cs-accent-deep)]">Edit</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-[var(--cs-muted)]">No purchases yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $purchases->links() }}</div>
    </section>
@endsection
