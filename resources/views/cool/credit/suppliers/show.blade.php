@extends('cool.layouts.app')

@section('title', 'Credit · '.$supplier->name)
@section('heading', 'Credit · '.$supplier->name)

@section('actions')
    <a href="{{ route('cool.credit.suppliers.index') }}" class="cs-btn-ghost">Back to list</a>
@endsection

@section('content')
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="cs-stat">
            <div class="text-xs uppercase tracking-[0.14em] text-[var(--cs-muted)]">Opening balance</div>
            <div class="brand-font mt-2 text-3xl font-bold">{{ number_format($supplier->opening_balance, 0) }}</div>
        </div>
        <div class="cs-stat">
            <div class="text-xs uppercase tracking-[0.14em] text-[var(--cs-muted)]">Accounts payable due</div>
            <div class="brand-font mt-2 text-3xl font-bold text-rose-700">{{ number_format($due, 0) }}</div>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
        <section class="cs-panel overflow-hidden">
            <div class="px-5 py-4 border-b border-[var(--cs-line)] brand-font text-lg font-bold">Open credit purchases</div>
            <table class="cs-table">
                <thead>
                    <tr>
                        <th>Purchase no</th>
                        <th>Purchased</th>
                        <th>Due date</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Remaining</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($openPurchases as $purchase)
                        <tr>
                            <td class="font-semibold">{{ $purchase->purchase_no }}</td>
                            <td>{{ optional($purchase->purchased_at)->format('d M Y') }}</td>
                            <td>{{ optional($purchase->due_date)->format('d M Y') ?: '—' }}</td>
                            <td>{{ number_format($purchase->total, 0) }}</td>
                            <td>{{ number_format($purchase->amount_paid, 0) }}</td>
                            <td class="font-semibold text-rose-700">{{ number_format(max(0, $purchase->total - $purchase->amount_paid), 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-[var(--cs-muted)]">No open credit purchases.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <form method="POST" action="{{ route('cool.suppliers.pay', $supplier->id) }}" class="cs-panel p-5 space-y-3 h-fit">
            @csrf
            <h2 class="brand-font text-lg font-bold">Record AP payment</h2>
            <input class="cs-input" type="number" step="0.01" name="amount" placeholder="Amount" required>
            <select class="cs-input" name="method">
                <option value="cash">Cash</option>
                <option value="transfer">Transfer</option>
                <option value="card">Card</option>
            </select>
            <select class="cs-input" name="purchase_id">
                <option value="">Unallocated / general</option>
                @foreach ($openPurchases as $purchase)
                    <option value="{{ $purchase->id }}">
                        {{ $purchase->purchase_no }} · due {{ number_format($purchase->total - $purchase->amount_paid, 0) }}
                    </option>
                @endforeach
            </select>
            <input class="cs-input" name="reference" placeholder="Reference">
            <button class="cs-btn-primary" type="submit">Save payment</button>
        </form>
    </div>

    <section class="cs-panel overflow-hidden">
        <div class="px-5 py-4 border-b border-[var(--cs-line)] brand-font text-lg font-bold">Payment history</div>
        <table class="cs-table">
            <thead><tr><th>Date</th><th>Method</th><th>Amount</th><th>Reference</th></tr></thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td>{{ optional($payment->paid_at)->format('d M Y H:i') }}</td>
                        <td class="capitalize">{{ $payment->method }}</td>
                        <td>{{ number_format($payment->amount, 0) }}</td>
                        <td>{{ $payment->reference ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-8 text-center text-[var(--cs-muted)]">No payments.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $payments->links() }}</div>
    </section>
@endsection
