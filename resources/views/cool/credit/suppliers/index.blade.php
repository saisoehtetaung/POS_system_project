@extends('cool.layouts.app')

@section('title', 'Credit Suppliers')
@section('heading', 'Credit · Suppliers')

@section('content')
    <section class="cs-panel overflow-hidden">
        <div class="px-5 py-4 border-b border-[var(--cs-line)]">
            <div class="brand-font text-lg font-bold">Suppliers with outstanding payables</div>
            <p class="mt-1 text-sm text-[var(--cs-muted)]">Credit purchases and opening balances still due.</p>
        </div>
        <table class="cs-table">
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th>Phone</th>
                    <th>Oldest due</th>
                    <th>Payable due</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td class="font-semibold">{{ $supplier->name }}</td>
                        <td>{{ $supplier->phone ?: '—' }}</td>
                        <td>
                            @if($supplier->oldest_due_at)
                                {{ \Illuminate\Support\Carbon::parse($supplier->oldest_due_at)->format('d M Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="font-semibold text-rose-700">{{ number_format($supplier->payable_due, 0) }}</td>
                        <td class="text-right">
                            <a class="text-sm font-semibold text-[var(--cs-accent-deep)]" href="{{ route('cool.credit.suppliers.show', $supplier->id) }}">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-[var(--cs-muted)]">No suppliers currently owed.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
