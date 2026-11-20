@extends('cool.layouts.app')

@section('title', $customer->name)
@section('heading', $customer->name)

@section('actions')
    <a href="{{ route('cool.customers.index') }}" class="cs-btn-ghost">Back</a>
@endsection

@section('content')
    <div class="grid gap-4 xl:grid-cols-3">
        <div class="cs-stat">
            <div class="text-xs uppercase tracking-[0.14em] text-[var(--cs-muted)]">Credit limit</div>
            <div class="brand-font mt-2 text-3xl font-bold">{{ number_format($customer->credit_limit, 0) }}</div>
        </div>
        <div class="cs-stat">
            <div class="text-xs uppercase tracking-[0.14em] text-[var(--cs-muted)]">Outstanding</div>
            <div class="brand-font mt-2 text-3xl font-bold">{{ number_format($customer->balance, 0) }}</div>
        </div>
        <div class="cs-stat">
            <div class="text-xs uppercase tracking-[0.14em] text-[var(--cs-muted)]">Available</div>
            <div class="brand-font mt-2 text-3xl font-bold">{{ number_format(max(0, $customer->credit_limit - $customer->balance), 0) }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('cool.customers.repay', $customer->id) }}" class="cs-panel p-5 space-y-3 max-w-xl">
        @csrf
        <h2 class="brand-font text-lg font-bold">Record repayment</h2>
        <input class="cs-input" type="number" step="0.01" name="amount" placeholder="Amount" required>
        <select class="cs-input" name="method">
            <option value="cash">Cash</option>
            <option value="transfer">Transfer</option>
            <option value="card">Card</option>
        </select>
        <input class="cs-input" name="note" placeholder="Note">
        <button class="cs-btn-primary" type="submit">Save repayment</button>
    </form>

    <section class="cs-panel overflow-hidden">
        <div class="px-5 py-4 border-b border-[var(--cs-line)] brand-font text-lg font-bold">Credit ledger</div>
        <table class="cs-table">
            <thead>
                <tr><th>Date</th><th>Type</th><th>Debit</th><th>Credit</th><th>Balance</th></tr>
            </thead>
            <tbody>
                @forelse ($ledger as $entry)
                    <tr>
                        <td>{{ optional($entry->occurred_at)->format('d M Y H:i') }}</td>
                        <td class="capitalize">{{ str_replace('_', ' ', $entry->entry_type) }}</td>
                        <td>{{ number_format($entry->debit, 0) }}</td>
                        <td>{{ number_format($entry->credit, 0) }}</td>
                        <td>{{ number_format($entry->balance_after, 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-[var(--cs-muted)]">No ledger entries.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $ledger->links() }}</div>
    </section>
@endsection
