@extends('cool.layouts.app')

@section('title', $purchase->purchase_no)
@section('heading', $purchase->purchase_no)

@section('actions')
    <a href="{{ route('cool.purchases.index') }}" class="cs-btn-ghost">Back</a>
    @if($purchase->status === 'draft')
        <a href="{{ route('cool.purchases.edit', $purchase->id) }}" class="cs-btn-ghost">Edit</a>
        <form method="POST" action="{{ route('cool.purchases.receive', $purchase->id) }}" class="inline" onsubmit="return confirm('Receive this purchase and update stock?')">
            @csrf
            <button class="cs-btn-primary" type="submit">Receive</button>
        </form>
        <form method="POST" action="{{ route('cool.purchases.cancel', $purchase->id) }}" class="inline" onsubmit="return confirm('Cancel this draft?')">
            @csrf
            <button class="cs-btn-ghost text-rose-600" type="submit">Cancel draft</button>
        </form>
    @endif
@endsection

@section('content')
    <div class="grid gap-4 md:grid-cols-4">
        <div class="cs-stat">
            <div class="text-xs uppercase tracking-[0.14em] text-[var(--cs-muted)]">Supplier</div>
            <div class="brand-font mt-2 text-xl font-bold">{{ optional($purchase->supplier)->name }}</div>
        </div>
        <div class="cs-stat">
            <div class="text-xs uppercase tracking-[0.14em] text-[var(--cs-muted)]">Status</div>
            <div class="brand-font mt-2 text-xl font-bold capitalize">{{ $purchase->status }}</div>
        </div>
        <div class="cs-stat">
            <div class="text-xs uppercase tracking-[0.14em] text-[var(--cs-muted)]">Payment</div>
            <div class="brand-font mt-2 text-xl font-bold capitalize">{{ $purchase->payment_type }}</div>
            @if($purchase->payment_type === 'credit')
                <div class="mt-1 text-xs text-[var(--cs-muted)]">Due {{ optional($purchase->due_date)->format('d M Y') }}</div>
            @endif
        </div>
        <div class="cs-stat">
            <div class="text-xs uppercase tracking-[0.14em] text-[var(--cs-muted)]">Total</div>
            <div class="brand-font mt-2 text-xl font-bold">{{ number_format($purchase->total, 0) }}</div>
            <div class="mt-1 text-xs text-[var(--cs-muted)]">Paid {{ number_format($purchase->amount_paid, 0) }}</div>
        </div>
    </div>

    @if($purchase->notes)
        <div class="cs-panel p-4 text-sm text-[var(--cs-muted)]">{{ $purchase->notes }}</div>
    @endif

    <section class="cs-panel overflow-hidden">
        <div class="px-5 py-4 border-b border-[var(--cs-line)] brand-font text-lg font-bold">Items</div>
        <table class="cs-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Unit</th>
                    <th>Qty</th>
                    <th>Unit cost</th>
                    <th>Line total</th>
                    <th>Lot / Expiry</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $item)
                    <tr>
                        <td>
                            <div class="font-semibold">{{ optional(optional($item->variant)->product)->name }}</div>
                            <div class="text-xs text-[var(--cs-muted)]">{{ optional($item->variant)->name }} · {{ optional($item->variant)->sku }}</div>
                        </td>
                        <td>{{ optional($item->unit)->abbreviation }}</td>
                        <td>{{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }}</td>
                        <td>{{ number_format($item->unit_cost, 0) }}</td>
                        <td>{{ number_format($item->line_total, 0) }}</td>
                        <td class="text-xs text-[var(--cs-muted)]">
                            {{ $item->lot_code ?: '—' }}
                            @if($item->expiry_date)
                                · {{ $item->expiry_date->format('d M Y') }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    @if($purchase->payments->isNotEmpty())
        <section class="cs-panel overflow-hidden">
            <div class="px-5 py-4 border-b border-[var(--cs-line)] brand-font text-lg font-bold">Payments</div>
            <table class="cs-table">
                <thead><tr><th>Date</th><th>Method</th><th>Amount</th><th>Reference</th></tr></thead>
                <tbody>
                    @foreach ($purchase->payments as $payment)
                        <tr>
                            <td>{{ optional($payment->paid_at)->format('d M Y H:i') }}</td>
                            <td class="capitalize">{{ $payment->method }}</td>
                            <td>{{ number_format($payment->amount, 0) }}</td>
                            <td>{{ $payment->reference ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif
@endsection
