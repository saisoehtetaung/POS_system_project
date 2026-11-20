@extends('cool.layouts.app')

@section('title', 'Credit Customers')
@section('heading', 'Credit · Customers')

@section('content')
    <section class="cs-panel overflow-hidden">
        <div class="px-5 py-4 border-b border-[var(--cs-line)]">
            <div class="brand-font text-lg font-bold">Customers with outstanding credit</div>
            <p class="mt-1 text-sm text-[var(--cs-muted)]">Only accounts that still owe a balance.</p>
        </div>
        <table class="cs-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Last credit sale</th>
                    <th>Outstanding</th>
                    <th>Available</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    @php
                        $lastAt = $lastSaleDates[$customer->id] ?? null;
                    @endphp
                    <tr>
                        <td class="font-semibold">{{ $customer->name }}</td>
                        <td>{{ $customer->phone ?: '—' }}</td>
                        <td>{{ $lastAt ? \Illuminate\Support\Carbon::parse($lastAt)->format('d M Y') : '—' }}</td>
                        <td class="font-semibold text-rose-700">{{ number_format($customer->balance, 0) }}</td>
                        <td>{{ number_format(max(0, $customer->credit_limit - $customer->balance), 0) }}</td>
                        <td class="text-right">
                            <a class="text-sm font-semibold text-[var(--cs-accent-deep)]" href="{{ route('cool.credit.customers.show', $customer->id) }}">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-[var(--cs-muted)]">No customers currently on credit.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $customers->links() }}</div>
    </section>
@endsection
