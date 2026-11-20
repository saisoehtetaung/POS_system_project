@extends('cool.layouts.app')

@section('title', 'POS Billing')
@section('heading', 'POS Billing')

@section('content')
    <div
        class="grid gap-4 xl:grid-cols-[1.4fr_0.9fr]"
        x-data="coolPos(@js([
            'variants' => $variants,
            'customers' => $customers,
            'shop' => $shop,
            'cashier' => $cashier,
            'checkoutUrl' => $checkoutUrl,
        ]))"
        @keydown.window.f8.prevent="checkout()"
    >
        <section class="space-y-4">
            <div class="cs-panel p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="brand-font text-xl font-bold">Welcome to Cool Stationary</div>
                        <div class="text-sm text-[var(--cs-muted)]">
                            {{ now()->format('d M Y, h:i A') }}
                            · Seller: {{ $cashier['name'] }} ({{ $cashier['login_id'] }})
                        </div>
                    </div>
                    <input
                        type="text"
                        class="cs-input sm:max-w-sm"
                        placeholder="Scan barcode / search SKU"
                        x-model="query"
                        @keydown.enter.prevent="addByCode()"
                        autofocus
                    >
                </div>

                <div class="mt-4 flex gap-2 overflow-x-auto pb-1">
                    <button type="button" class="cs-btn-ghost whitespace-nowrap" @click="setCategory(null)">All</button>
                    @foreach ($categories as $category)
                        <button type="button" class="cs-btn-ghost whitespace-nowrap" @click="setCategory('{{ $category->id }}')">{{ $category->name }}</button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <template x-for="item in paged" :key="item.id">
                    <button
                        type="button"
                        class="cs-panel text-left p-3 transition"
                        :class="isOutOfStock(item) ? 'opacity-40 cursor-not-allowed' : 'hover:border-[var(--cs-accent)]'"
                        :disabled="isOutOfStock(item)"
                        @click="addItem(item)"
                    >
                        <img :src="item.image_url" alt="" class="mb-3 h-28 w-full rounded-xl object-cover bg-slate-50 border border-[var(--cs-line)]">
                        <div class="brand-font font-bold" x-text="item.product_name"></div>
                        <div class="mt-1 text-sm text-[var(--cs-muted)]" x-text="item.name"></div>
                        <div class="mt-2 text-xs" :class="isOutOfStock(item) ? 'text-rose-600' : 'text-[var(--cs-muted)]'">
                            <span x-show="item.track_stock">Stock: <span x-text="item.qty_on_hand"></span></span>
                            <span x-show="!item.track_stock">Not tracked</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-2">
                            <span class="text-xs text-[var(--cs-muted)]" x-text="item.sku"></span>
                            <span class="font-bold text-sm" x-text="formatMoney(defaultPrice(item))"></span>
                        </div>
                    </button>
                </template>
            </div>

            <div class="flex items-center justify-between cs-panel px-4 py-3">
                <button type="button" class="cs-btn-ghost" @click="prevPage()" :disabled="page <= 1">Previous</button>
                <div class="text-sm text-[var(--cs-muted)]">
                    Page <span x-text="page"></span> / <span x-text="totalPages"></span>
                    · <span x-text="filtered.length"></span> items
                </div>
                <button type="button" class="cs-btn-ghost" @click="nextPage()" :disabled="page >= totalPages">Next</button>
            </div>
        </section>

        <aside class="cs-panel p-4 flex flex-col min-h-[70vh]">
            <div class="flex items-center justify-between">
                <h2 class="brand-font text-lg font-bold">Order Details</h2>
                <button type="button" class="text-sm font-semibold text-rose-600" @click="clearCart()">Clear</button>
            </div>

            <div class="mt-4 space-y-2">
                <label class="text-sm font-semibold">Customer</label>
                <select class="cs-input" x-model="customerId" @change="onCustomerChange()">
                    <option value="">Walk-in (cash only)</option>
                    <template x-for="customer in customers" :key="customer.id">
                        <option :value="customer.id" x-text="`${customer.name}${customer.phone ? ' · ' + customer.phone : ''}`"></option>
                    </template>
                </select>
                <template x-if="selectedCustomer">
                    <div class="rounded-xl bg-[var(--cs-paper)] px-3 py-2 text-xs text-[var(--cs-muted)]">
                        Outstanding: <span class="font-semibold text-slate-700" x-text="formatMoney(selectedCustomer.balance)"></span>
                        · Available credit: <span class="font-semibold text-slate-700" x-text="formatMoney(selectedCustomer.available_credit)"></span>
                    </div>
                </template>
            </div>

            <div class="mt-4 flex-1 space-y-3 overflow-y-auto">
                <template x-if="cart.length === 0">
                    <div class="rounded-2xl bg-[var(--cs-paper)] px-4 py-10 text-center text-sm text-[var(--cs-muted)]">
                        No products selected
                    </div>
                </template>

                <template x-for="line in cart" :key="line.key">
                    <div class="rounded-2xl border border-[var(--cs-line)] p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold" x-text="line.product_name"></div>
                                <div class="text-xs text-[var(--cs-muted)]" x-text="line.name"></div>
                            </div>
                            <button type="button" class="text-xs text-rose-600" @click="removeItem(line.key)">Remove</button>
                        </div>

                        <div class="mt-2">
                            <select class="cs-input" x-model="line.unit_id" @change="changeUnit(line)">
                                <template x-for="unit in line.unit_prices" :key="unit.unit_id">
                                    <option :value="unit.unit_id" x-text="`${unit.unit_name} · ${formatMoney(unit.price)}`"></option>
                                </template>
                            </select>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <button type="button" class="cs-btn-ghost px-2 py-1" @click="changeQty(line.key, -1)">-</button>
                                <span class="min-w-[2rem] text-center font-semibold" x-text="line.qty"></span>
                                <button type="button" class="cs-btn-ghost px-2 py-1" @click="changeQty(line.key, 1)">+</button>
                            </div>
                            <div class="font-bold" x-text="formatMoney(line.qty * line.unit_price)"></div>
                        </div>
                        <div class="mt-1 text-xs text-[var(--cs-muted)]">
                            <span x-text="line.unit_name"></span>
                            · base <span x-text="line.qty * line.factor_to_base"></span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-4 border-t border-[var(--cs-line)] pt-4 space-y-2">
                <template x-if="selectedCustomer">
                    <div class="space-y-2 pb-2">
                        <label class="inline-flex items-center gap-2 text-sm font-semibold">
                            <input type="checkbox" x-model="useCredit" @change="onCreditToggle()">
                            Credit / ကြွေး
                        </label>
                        <template x-if="useCredit">
                            <div>
                                <label class="text-xs font-semibold text-[var(--cs-muted)]">Amount paid now</label>
                                <input type="number" min="0" step="1" class="cs-input mt-1" x-model.number="amountPaid">
                                <div class="mt-1 text-xs text-[var(--cs-muted)]">
                                    Due on credit: <span class="font-semibold" x-text="formatMoney(amountDue)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="flex justify-between text-sm">
                    <span>Sub Total</span>
                    <span x-text="formatMoney(subtotal)"></span>
                </div>
                <div class="flex justify-between text-sm" x-show="taxAmount > 0">
                    <span>Tax (<span x-text="shop.tax_rate"></span>%)</span>
                    <span x-text="formatMoney(taxAmount)"></span>
                </div>
                <div class="flex justify-between brand-font text-2xl font-bold">
                    <span>Total</span>
                    <span x-text="formatMoney(total)"></span>
                </div>
                <button type="button" class="cs-btn-primary w-full mt-2" :disabled="busy" @click="checkout()">
                    <span x-text="busy ? 'Processing…' : 'Checkout (F8)'"></span>
                </button>
                <p class="text-xs text-[var(--cs-muted)]" x-text="hint"></p>
            </div>
        </aside>
    </div>

    <script>
        function coolPos(boot) {
            return {
                query: '',
                categoryId: null,
                cart: [],
                page: 1,
                perPage: 8,
                hint: 'Pick items. Select a customer to enable credit. F8 checkout.',
                busy: false,
                variants: boot.variants || [],
                customers: boot.customers || [],
                shop: boot.shop || { tax_rate: 0, currency_code: 'MMK' },
                cashier: boot.cashier || {},
                checkoutUrl: boot.checkoutUrl,
                customerId: '',
                useCredit: false,
                amountPaid: 0,
                get filtered() {
                    return this.variants.filter((item) => {
                        const matchesCategory = !this.categoryId || item.category_id === this.categoryId;
                        const q = this.query.trim().toLowerCase();
                        const matchesQuery = !q
                            || item.name.toLowerCase().includes(q)
                            || item.product_name.toLowerCase().includes(q)
                            || (item.sku || '').toLowerCase().includes(q)
                            || (item.barcode || '').toLowerCase().includes(q);
                        return matchesCategory && matchesQuery;
                    });
                },
                get totalPages() {
                    return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
                },
                get paged() {
                    const start = (this.page - 1) * this.perPage;
                    return this.filtered.slice(start, start + this.perPage);
                },
                get selectedCustomer() {
                    if (!this.customerId) return null;
                    return this.customers.find((item) => item.id === this.customerId) || null;
                },
                get subtotal() {
                    return this.cart.reduce((sum, line) => sum + (line.qty * line.unit_price), 0);
                },
                get taxAmount() {
                    const rate = Number(this.shop.tax_rate || 0);
                    if (rate <= 0) return 0;
                    return Math.round(this.subtotal * (rate / 100));
                },
                get total() {
                    return this.subtotal + this.taxAmount;
                },
                get amountDue() {
                    if (!this.useCredit) return 0;
                    return Math.max(0, this.total - Number(this.amountPaid || 0));
                },
                formatMoney(value) {
                    return new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(value || 0) + ' ' + (this.shop.currency_code || 'MMK');
                },
                isOutOfStock(item) {
                    return !!item.track_stock && Number(item.qty_on_hand || 0) <= 0;
                },
                availableBase(item) {
                    if (!item.track_stock) return Infinity;
                    return Number(item.qty_on_hand || 0);
                },
                cartBaseForVariant(variantId, exceptKey = null) {
                    return this.cart
                        .filter((line) => line.id === variantId && line.key !== exceptKey)
                        .reduce((sum, line) => sum + (line.qty * line.factor_to_base), 0);
                },
                defaultPrice(item) {
                    return item.unit_prices?.[0]?.price ?? item.retail_price;
                },
                selectedUnit(item, unitId = null) {
                    const prices = item.unit_prices || [];
                    return prices.find((unit) => unit.unit_id === unitId) || prices[0];
                },
                setCategory(id) {
                    this.categoryId = id;
                    this.page = 1;
                },
                prevPage() {
                    this.page = Math.max(1, this.page - 1);
                },
                nextPage() {
                    this.page = Math.min(this.totalPages, this.page + 1);
                },
                onCustomerChange() {
                    if (!this.customerId) {
                        this.useCredit = false;
                        this.amountPaid = this.total;
                    }
                },
                onCreditToggle() {
                    if (this.useCredit) {
                        this.amountPaid = 0;
                    } else {
                        this.amountPaid = this.total;
                    }
                },
                clearCart() {
                    this.cart = [];
                    this.useCredit = false;
                    this.amountPaid = 0;
                },
                addItem(item) {
                    if (this.isOutOfStock(item)) {
                        this.hint = item.product_name + ' is out of stock.';
                        return;
                    }

                    const unit = this.selectedUnit(item);
                    const key = item.id + ':' + unit.unit_id;
                    const existing = this.cart.find((line) => line.key === key);
                    const nextQty = existing ? existing.qty + 1 : 1;
                    const nextBase = this.cartBaseForVariant(item.id, key) + (nextQty * unit.factor_to_base);

                    if (item.track_stock && nextBase > this.availableBase(item) + 0.0001) {
                        this.hint = 'Not enough stock for ' + item.product_name;
                        return;
                    }

                    if (existing) {
                        existing.qty = nextQty;
                        return;
                    }

                    this.cart.push({
                        key,
                        id: item.id,
                        product_name: item.product_name,
                        name: item.name,
                        qty: 1,
                        unit_id: unit.unit_id,
                        unit_name: unit.unit_name,
                        unit_price: unit.price,
                        factor_to_base: unit.factor_to_base,
                        unit_prices: item.unit_prices,
                        track_stock: item.track_stock,
                        qty_on_hand: item.qty_on_hand,
                    });
                },
                addByCode() {
                    const code = this.query.trim().toLowerCase();
                    if (!code) return;
                    const item = this.variants.find((variant) =>
                        (variant.barcode || '').toLowerCase() === code || (variant.sku || '').toLowerCase() === code
                    );
                    if (item) {
                        this.addItem(item);
                        this.query = '';
                        this.hint = 'Added ' + item.product_name;
                    } else {
                        this.hint = 'No item found for ' + this.query;
                    }
                },
                changeUnit(line) {
                    const unit = line.unit_prices.find((item) => item.unit_id === line.unit_id);
                    if (!unit) return;
                    const source = this.variants.find((item) => item.id === line.id);
                    const nextBase = this.cartBaseForVariant(line.id, line.key) + (line.qty * unit.factor_to_base);
                    if (source?.track_stock && nextBase > this.availableBase(source) + 0.0001) {
                        this.hint = 'Not enough stock for that unit.';
                        return;
                    }
                    line.unit_name = unit.unit_name;
                    line.unit_price = unit.price;
                    line.factor_to_base = unit.factor_to_base;
                    line.key = line.id + ':' + line.unit_id;
                },
                changeQty(key, delta) {
                    const line = this.cart.find((item) => item.key === key);
                    if (!line) return;
                    const nextQty = Math.max(1, line.qty + delta);
                    const source = this.variants.find((item) => item.id === line.id);
                    const nextBase = this.cartBaseForVariant(line.id, key) + (nextQty * line.factor_to_base);
                    if (delta > 0 && source?.track_stock && nextBase > this.availableBase(source) + 0.0001) {
                        this.hint = 'Not enough stock.';
                        return;
                    }
                    line.qty = nextQty;
                },
                removeItem(key) {
                    this.cart = this.cart.filter((item) => item.key !== key);
                },
                async checkout() {
                    if (this.busy) return;
                    if (!this.cart.length) {
                        this.hint = 'Add at least one item before checkout.';
                        return;
                    }

                    if (this.useCredit && !this.customerId) {
                        this.hint = 'Select a customer for credit sales.';
                        return;
                    }

                    if (this.useCredit) {
                        const paid = Number(this.amountPaid || 0);
                        if (paid < 0 || paid > this.total) {
                            this.hint = 'Amount paid must be between 0 and total.';
                            return;
                        }
                        if (this.selectedCustomer && this.amountDue > this.selectedCustomer.available_credit + 0.0001) {
                            this.hint = 'Credit exceeds available limit.';
                            return;
                        }
                    }

                    this.busy = true;
                    this.hint = 'Saving sale…';

                    try {
                        const response = await fetch(this.checkoutUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                cart: this.cart.map((line) => ({
                                    variant_id: line.id,
                                    unit_id: line.unit_id,
                                    unit_name: line.unit_name,
                                    qty: line.qty,
                                    unit_price: line.unit_price,
                                    factor_to_base: line.factor_to_base,
                                })),
                                customer_id: this.customerId || null,
                                use_credit: !!this.useCredit,
                                amount_paid: this.useCredit ? Number(this.amountPaid || 0) : this.total,
                            }),
                        });

                        const payload = await response.json();
                        if (!response.ok) {
                            this.hint = payload.message || 'Checkout failed.';
                            this.busy = false;
                            return;
                        }

                        window.location.href = payload.receipt_url;
                    } catch (error) {
                        this.hint = 'Network error during checkout.';
                        this.busy = false;
                    }
                },
            }
        }
    </script>
@endsection
