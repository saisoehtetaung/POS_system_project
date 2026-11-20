@extends('cool.layouts.app')

@section('title', 'Customers')
@section('heading', 'Customers')

@section('content')
    <div
        class="space-y-4"
        x-data="customerModal({
            storeUrl: @js(route('cool.customers.store')),
            updateUrl: @js(url('cool/customers')),
            openOnLoad: @js($errors->any()),
            old: @js(old()),
        })"
    >
        <div class="flex justify-end">
            <button type="button" class="cs-btn-primary" @click="openCreate()">Add Customer</button>
        </div>

        <section class="cs-panel overflow-hidden">
            <table class="cs-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Limit</th>
                        <th>Balance</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $customer->name }}</div>
                                <div class="text-xs text-[var(--cs-muted)]">{{ $customer->phone }}</div>
                            </td>
                            <td class="uppercase text-xs">{{ $customer->customer_type }}</td>
                            <td>{{ number_format($customer->credit_limit, 0) }}</td>
                            <td>{{ number_format($customer->balance, 0) }}</td>
                            <td class="text-right space-x-3">
                                <button
                                    type="button"
                                    class="text-sm font-semibold text-[var(--cs-accent-deep)]"
                                    @click="openEdit(@js([
                                        'id' => $customer->id,
                                        'name' => $customer->name,
                                        'phone' => $customer->phone,
                                        'email' => $customer->email,
                                        'address' => $customer->address,
                                        'customer_type' => $customer->customer_type,
                                        'credit_limit' => $customer->credit_limit,
                                        'is_active' => (bool) $customer->is_active,
                                    ]))"
                                >Edit</button>
                                <a href="{{ route('cool.customers.show', $customer->id) }}" class="text-sm font-semibold text-[var(--cs-accent-deep)]">Credit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-[var(--cs-muted)]">No customers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $customers->links() }}</div>
        </section>

        <div class="cs-modal-backdrop" x-show="open" x-cloak @keydown.escape.window="open = false">
            <div class="cs-modal" @click.outside="open = false">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="brand-font text-lg font-bold" x-text="editing ? 'Edit Customer' : 'Add Customer'"></h2>
                    <button type="button" class="cs-btn-ghost px-3 py-1" @click="open = false">Close</button>
                </div>
                <form method="POST" :action="formAction" class="space-y-3">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" :disabled="!editing">
                    <input class="cs-input" name="name" placeholder="Name" x-model="form.name" required>
                    <input class="cs-input" name="phone" placeholder="Phone" x-model="form.phone">
                    <input class="cs-input" name="email" type="email" placeholder="Email" x-model="form.email">
                    <textarea class="cs-input" name="address" rows="2" placeholder="Address" x-model="form.address"></textarea>
                    <select class="cs-input" name="customer_type" x-model="form.customer_type">
                        <option value="regular">Regular</option>
                        <option value="b2b">B2B</option>
                    </select>
                    <input class="cs-input" name="credit_limit" type="number" step="0.01" placeholder="Credit limit" x-model="form.credit_limit">
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
        function customerModal(boot) {
            return {
                open: !!boot.openOnLoad,
                editing: false,
                storeUrl: boot.storeUrl,
                updateUrl: boot.updateUrl,
                form: { id: null, name: '', phone: '', email: '', address: '', customer_type: 'regular', credit_limit: 0, is_active: true },
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
                            customer_type: boot.old.customer_type || 'regular',
                            credit_limit: boot.old.credit_limit ?? 0,
                            is_active: !!boot.old.is_active,
                        };
                        this.editing = !!this.form.id;
                    }
                },
                openCreate() {
                    this.editing = false;
                    this.form = { id: null, name: '', phone: '', email: '', address: '', customer_type: 'regular', credit_limit: 0, is_active: true };
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
                        customer_type: item.customer_type || 'regular',
                        credit_limit: item.credit_limit ?? 0,
                        is_active: !!item.is_active,
                    };
                    this.open = true;
                },
            };
        }
    </script>
@endsection
