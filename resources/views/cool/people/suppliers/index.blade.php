@extends('cool.layouts.app')

@section('title', 'Suppliers')
@section('heading', 'Suppliers')

@section('content')
    <div
        class="space-y-4"
        x-data="supplierModal({
            storeUrl: @js(route('cool.suppliers.store')),
            updateUrl: @js(url('cool/suppliers')),
            openOnLoad: @js($errors->any()),
            old: @js(old()),
        })"
    >
        <div class="flex justify-end">
            <button type="button" class="cs-btn-primary" @click="openCreate()">Add Supplier</button>
        </div>

        <section class="cs-panel overflow-hidden">
            <table class="cs-table">
                <thead>
                    <tr><th>Name</th><th>Phone</th><th>Opening</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="font-semibold">{{ $supplier->name }}</td>
                            <td>{{ $supplier->phone ?? '—' }}</td>
                            <td>{{ number_format($supplier->opening_balance, 0) }}</td>
                            <td class="text-right space-x-3">
                                <button
                                    type="button"
                                    class="text-sm font-semibold text-[var(--cs-accent-deep)]"
                                    @click="openEdit(@js([
                                        'id' => $supplier->id,
                                        'name' => $supplier->name,
                                        'phone' => $supplier->phone,
                                        'email' => $supplier->email,
                                        'address' => $supplier->address,
                                        'opening_balance' => $supplier->opening_balance,
                                        'is_active' => (bool) $supplier->is_active,
                                    ]))"
                                >Edit</button>
                                <a href="{{ route('cool.suppliers.show', $supplier->id) }}" class="text-sm font-semibold text-[var(--cs-accent-deep)]">AP / Credit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-[var(--cs-muted)]">No suppliers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $suppliers->links() }}</div>
        </section>

        <div class="cs-modal-backdrop" x-show="open" x-cloak @keydown.escape.window="open = false">
            <div class="cs-modal" @click.outside="open = false">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="brand-font text-lg font-bold" x-text="editing ? 'Edit Supplier' : 'Add Supplier'"></h2>
                    <button type="button" class="cs-btn-ghost px-3 py-1" @click="open = false">Close</button>
                </div>
                <form method="POST" :action="formAction" class="space-y-3">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" :disabled="!editing">
                    <input class="cs-input" name="name" placeholder="Supplier name" x-model="form.name" required>
                    <input class="cs-input" name="phone" placeholder="Phone" x-model="form.phone">
                    <input class="cs-input" name="email" type="email" placeholder="Email" x-model="form.email">
                    <textarea class="cs-input" name="address" rows="2" placeholder="Address" x-model="form.address"></textarea>
                    <input class="cs-input" type="number" step="0.01" name="opening_balance" placeholder="Opening balance" x-model="form.opening_balance">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active">
                        Active
                    </label>
                    <button class="cs-btn-primary w-full" type="submit" x-text="editing ? 'Update' : 'Save'"></button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function supplierModal(boot) {
            return {
                open: !!boot.openOnLoad,
                editing: false,
                storeUrl: boot.storeUrl,
                updateUrl: boot.updateUrl,
                form: { id: null, name: '', phone: '', email: '', address: '', opening_balance: 0, is_active: true },
                get formAction() {
                    return this.editing ? `${this.updateUrl}/${this.form.id}` : this.storeUrl;
                },
                init() {
                    if (boot.openOnLoad && boot.old) {
                        this.form = {
                            id: boot.old._editing_id || null,
                            name: boot.old.name || '',
                            phone: boot.old.phone || '',
                            email: boot.old.email || '',
                            address: boot.old.address || '',
                            opening_balance: boot.old.opening_balance ?? 0,
                            is_active: !!boot.old.is_active,
                        };
                        this.editing = !!this.form.id;
                    }
                },
                openCreate() {
                    this.editing = false;
                    this.form = { id: null, name: '', phone: '', email: '', address: '', opening_balance: 0, is_active: true };
                    this.open = true;
                },
                openEdit(item) {
                    this.editing = true;
                    this.form = {
                        id: item.id,
                        name: item.name || '',
                        phone: item.phone || '',
                        email: item.email || '',
                        address: item.address || '',
                        opening_balance: item.opening_balance ?? 0,
                        is_active: !!item.is_active,
                    };
                    this.open = true;
                },
            };
        }
    </script>
@endsection
