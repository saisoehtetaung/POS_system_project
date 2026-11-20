@extends('cool.layouts.app')

@section('title', 'Stock')
@section('heading', 'Stock')

@section('content')
    <div
        class="space-y-4"
        x-data="stockAdjustModal({
            openOnLoad: @js($errors->any() && old('variant_id')),
        })"
    >
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('cool.stock.index', ['tab' => 'all']) }}" class="cs-btn-ghost {{ $tab === 'all' ? 'ring-2 ring-[var(--cs-accent)]' : '' }}">All balances</a>
                <a href="{{ route('cool.stock.index', ['tab' => 'low']) }}" class="cs-btn-ghost {{ $tab === 'low' ? 'ring-2 ring-[var(--cs-accent)]' : '' }}">Low stock ({{ $low->total() }})</a>
                <a href="{{ route('cool.stock.index', ['tab' => 'expired']) }}" class="cs-btn-ghost {{ $tab === 'expired' ? 'ring-2 ring-[var(--cs-accent)]' : '' }}">Expired ({{ $expired->total() }})</a>
            </div>
            @if(auth()->user()->hasRole('owner', 'manager'))
                <button type="button" class="cs-btn-primary" @click="open = true">Adjust Stock</button>
            @endif
        </div>

        @if($tab === 'all')
            <section class="cs-panel overflow-hidden">
                <table class="cs-table">
                    <thead><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Reorder</th></tr></thead>
                    <tbody>
                        @forelse ($balances as $balance)
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ optional($balance->variant->product)->name }}</div>
                                    <div class="text-xs text-[var(--cs-muted)]">{{ optional($balance->variant)->name }}</div>
                                </td>
                                <td>{{ optional($balance->variant)->sku }}</td>
                                <td>{{ number_format($balance->qty_on_hand, 3) }}</td>
                                <td>{{ number_format(optional($balance->variant)->reorder_level, 3) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-[var(--cs-muted)]">No stock balances.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-3">{{ $balances->links() }}</div>
            </section>
        @elseif($tab === 'low')
            <section class="cs-panel overflow-hidden">
                <table class="cs-table">
                    <thead><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Reorder</th></tr></thead>
                    <tbody>
                        @forelse ($low as $balance)
                            <tr>
                                <td class="font-semibold">{{ optional($balance->variant->product)->name }}</td>
                                <td>{{ optional($balance->variant)->sku }}</td>
                                <td><span class="cs-badge-warn">{{ number_format($balance->qty_on_hand, 3) }}</span></td>
                                <td>{{ number_format(optional($balance->variant)->reorder_level, 3) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-[var(--cs-muted)]">No low stock items.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-3">{{ $low->links() }}</div>
            </section>
        @else
            <section class="cs-panel overflow-hidden">
                <table class="cs-table">
                    <thead><tr><th>Product</th><th>Lot</th><th>Expiry</th><th>Qty</th></tr></thead>
                    <tbody>
                        @forelse ($expired as $lot)
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
                <div class="px-4 py-3">{{ $expired->links() }}</div>
            </section>
        @endif

        @if(auth()->user()->hasRole('owner', 'manager'))
            <div class="cs-modal-backdrop" x-show="open" x-cloak @keydown.escape.window="open = false">
                <div class="cs-modal" @click.outside="open = false">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h2 class="brand-font text-lg font-bold">Adjust Stock</h2>
                        <button type="button" class="cs-btn-ghost px-3 py-1" @click="open = false">Close</button>
                    </div>
                    <form method="POST" action="{{ route('cool.stock.adjust') }}" class="space-y-3">
                        @csrf
                        <select class="cs-input" name="variant_id" required>
                            <option value="">Variant</option>
                            @foreach ($variants as $variant)
                                <option value="{{ $variant->id }}" @selected(old('variant_id') == $variant->id)>
                                    {{ optional($variant->product)->name }} · {{ $variant->name }} ({{ $variant->sku }})
                                </option>
                            @endforeach
                        </select>
                        <select class="cs-input" name="movement_type" required>
                            <option value="audit_adjustment" @selected(old('movement_type', 'audit_adjustment') === 'audit_adjustment')>Audit adjustment</option>
                            <option value="damage" @selected(old('movement_type') === 'damage')>Damage</option>
                            <option value="loss" @selected(old('movement_type') === 'loss')>Loss</option>
                        </select>
                        <input class="cs-input" type="number" step="0.001" name="qty_delta" placeholder="Qty delta (+/-)" value="{{ old('qty_delta') }}" required>
                        <input class="cs-input" name="reason" placeholder="Reason" value="{{ old('reason') }}" required>
                        <button class="cs-btn-primary w-full" type="submit">Save adjustment</button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <script>
        function stockAdjustModal(boot) {
            return {
                open: !!boot.openOnLoad,
            };
        }
    </script>
@endsection
