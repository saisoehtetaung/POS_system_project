@extends('cool.layouts.app')

@section('title', $purchase ? 'Edit Purchase' : 'New Purchase')
@section('heading', $purchase ? 'Edit Purchase' : 'New Purchase')

@section('actions')
    <a href="{{ route('cool.purchases.index') }}" class="cs-btn-ghost">Back</a>
@endsection

@section('content')
    @php
        $initialLines = old('items');
        if (! $initialLines && $purchase) {
            $initialLines = $purchase->items->map(fn ($item) => [
                'variant_id' => $item->variant_id,
                'unit_id' => $item->unit_id,
                'quantity' => (float) $item->quantity,
                'unit_cost' => (float) $item->unit_cost,
                'lot_code' => $item->lot_code,
                'expiry_date' => optional($item->expiry_date)->format('Y-m-d'),
            ])->values()->all();
        }
        if (! $initialLines) {
            $initialLines = [[
                'variant_id' => '',
                'unit_id' => '',
                'quantity' => 1,
                'unit_cost' => 0,
                'lot_code' => '',
                'expiry_date' => '',
            ]];
        }
    @endphp

    <form
        method="POST"
        action="{{ $purchase ? route('cool.purchases.update', $purchase->id) : route('cool.purchases.store') }}"
        class="space-y-4"
        x-data="purchaseForm(@js([
            'variants' => $variants,
            'lines' => $initialLines,
            'paymentType' => old('payment_type', $purchase->payment_type ?? 'cash'),
        ]))"
    >
        @csrf
        @if($purchase)
            @method('PUT')
        @endif

        <section class="cs-panel p-5 grid gap-3 md:grid-cols-2">
            <div>
                <label class="text-sm font-semibold">Supplier</label>
                <select class="cs-input mt-1" name="supplier_id" required>
                    <option value="">Select supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchase->supplier_id ?? '') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold">Payment type</label>
                <select class="cs-input mt-1" name="payment_type" x-model="paymentType" required>
                    <option value="cash">Cash</option>
                    <option value="credit">Credit</option>
                </select>
            </div>
            <div x-show="paymentType === 'credit'">
                <label class="text-sm font-semibold">Due date</label>
                <input class="cs-input mt-1" type="date" name="due_date" value="{{ old('due_date', optional($purchase->due_date ?? null)->format('Y-m-d')) }}" :required="paymentType === 'credit'">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-semibold">Notes</label>
                <textarea class="cs-input mt-1" name="notes" rows="2">{{ old('notes', $purchase->notes ?? '') }}</textarea>
            </div>
        </section>

        <section class="cs-panel p-5 space-y-3">
            <div class="flex items-center justify-between gap-3">
                <h2 class="brand-font text-lg font-bold">Line items</h2>
                <button type="button" class="cs-btn-ghost" @click="addLine()">Add line</button>
            </div>

            <template x-for="(line, index) in lines" :key="index">
                <div class="rounded-2xl border border-[var(--cs-line)] p-3 grid gap-3 md:grid-cols-6">
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-[var(--cs-muted)]">Product</label>
                        <select class="cs-input mt-1" :name="`items[${index}][variant_id]`" x-model="line.variant_id" @change="onVariantChange(line)" required>
                            <option value="">Select variant</option>
                            <template x-for="variant in variants" :key="variant.id">
                                <option :value="variant.id" x-text="variant.label"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-[var(--cs-muted)]">Unit</label>
                        <select class="cs-input mt-1" :name="`items[${index}][unit_id]`" x-model="line.unit_id" required>
                            <option value="">Unit</option>
                            <template x-for="unit in unitsFor(line)" :key="unit.unit_id">
                                <option :value="unit.unit_id" x-text="unit.unit_name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-[var(--cs-muted)]">Qty</label>
                        <input class="cs-input mt-1" type="number" step="0.001" min="0.001" :name="`items[${index}][quantity]`" x-model.number="line.quantity" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-[var(--cs-muted)]">Unit cost</label>
                        <input class="cs-input mt-1" type="number" step="0.01" min="0" :name="`items[${index}][unit_cost]`" x-model.number="line.unit_cost" required>
                    </div>
                    <div class="flex items-end justify-between gap-2">
                        <div class="text-sm font-semibold" x-text="formatMoney(line.quantity * line.unit_cost)"></div>
                        <button type="button" class="text-sm text-rose-600" @click="removeLine(index)" x-show="lines.length > 1">Remove</button>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-[var(--cs-muted)]">Lot code</label>
                        <input class="cs-input mt-1" :name="`items[${index}][lot_code]`" x-model="line.lot_code" placeholder="Optional">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-[var(--cs-muted)]">Expiry</label>
                        <input class="cs-input mt-1" type="date" :name="`items[${index}][expiry_date]`" x-model="line.expiry_date">
                    </div>
                </div>
            </template>

            <div class="flex justify-end brand-font text-xl font-bold">
                Subtotal: <span class="ml-2" x-text="formatMoney(subtotal)"></span>
            </div>
        </section>

        <button class="cs-btn-primary" type="submit">{{ $purchase ? 'Update draft' : 'Save draft' }}</button>
    </form>

    <script>
        function purchaseForm(boot) {
            return {
                variants: boot.variants || [],
                lines: boot.lines || [],
                paymentType: boot.paymentType || 'cash',
                get subtotal() {
                    return this.lines.reduce((sum, line) => sum + (Number(line.quantity || 0) * Number(line.unit_cost || 0)), 0);
                },
                formatMoney(value) {
                    return new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(value || 0) + ' MMK';
                },
                unitsFor(line) {
                    const variant = this.variants.find((item) => item.id === line.variant_id);
                    return variant?.units || [];
                },
                onVariantChange(line) {
                    const units = this.unitsFor(line);
                    line.unit_id = units[0]?.unit_id || '';
                },
                addLine() {
                    this.lines.push({
                        variant_id: '',
                        unit_id: '',
                        quantity: 1,
                        unit_cost: 0,
                        lot_code: '',
                        expiry_date: '',
                    });
                },
                removeLine(index) {
                    if (this.lines.length <= 1) return;
                    this.lines.splice(index, 1);
                },
            };
        }
    </script>
@endsection
