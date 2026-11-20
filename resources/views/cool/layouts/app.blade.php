<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Cool Stationary</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
        $isManager = auth()->user()->hasRole('owner', 'manager');
        $canUsePos = auth()->user()->hasRole('manager', 'cashier');
        $inventoryOpen = request()->routeIs('cool.products.*', 'cool.categories.*', 'cool.brands.*', 'cool.units.*', 'cool.stock.*', 'cool.purchases.*');
        $creditOpen = request()->routeIs('cool.credit.*');
    @endphp
    <div
        class="cs-shell"
        :class="sidebarOpen ? 'sidebar-open' : 'sidebar-closed'"
        x-data="{
            sidebarOpen: true,
            inventoryOpen: {{ $inventoryOpen ? 'true' : 'false' }},
            creditOpen: {{ $creditOpen ? 'true' : 'false' }},
            init() {
                const saved = localStorage.getItem('cs-sidebar-open');
                if (saved !== null) {
                    this.sidebarOpen = saved === '1';
                } else {
                    this.sidebarOpen = window.matchMedia('(min-width: 1024px)').matches;
                }
                this.$watch('sidebarOpen', (value) => localStorage.setItem('cs-sidebar-open', value ? '1' : '0'));
            },
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
            },
            closeSidebarOnMobile() {
                if (window.matchMedia('(max-width: 1023px)').matches) {
                    this.sidebarOpen = false;
                }
            }
        }"
    >
        <div
            class="cs-sidebar-backdrop"
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
        ></div>

        <aside class="cs-sidebar" :class="sidebarOpen ? 'is-open' : 'is-closed'">
            <div class="cs-brand">
                <div class="cs-brand-mark">CS</div>
                <div>
                    <div class="brand-font text-lg font-bold leading-tight">Cool Stationary</div>
                    <div class="text-xs text-[var(--cs-muted)]">POS & Inventory</div>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto" @click="closeSidebarOnMobile()">
                @if($isManager)
                    <a class="cs-nav-link {{ request()->routeIs('cool.dashboard') ? 'is-active' : '' }}" href="{{ route('cool.dashboard') }}">Dashboard</a>
                @endif
                @if($canUsePos)
                    <a class="cs-nav-link {{ request()->routeIs('cool.pos.*') ? 'is-active' : '' }}" href="{{ route('cool.pos.index') }}">POS Billing</a>
                @endif

                <div>
                    <button
                        type="button"
                        class="cs-nav-link w-full justify-between"
                        :class="inventoryOpen ? 'is-active' : ''"
                        @click.stop="inventoryOpen = !inventoryOpen"
                    >
                        <span>Inventory</span>
                        <span class="text-xs" x-text="inventoryOpen ? '▾' : '▸'"></span>
                    </button>
                    <div class="cs-nav-sub" x-show="inventoryOpen" x-cloak>
                        <a class="cs-nav-link {{ request()->routeIs('cool.products.*') ? 'is-active' : '' }}" href="{{ route('cool.products.index') }}">Products</a>
                        @if($isManager)
                            <a class="cs-nav-link {{ request()->routeIs('cool.categories.*') ? 'is-active' : '' }}" href="{{ route('cool.categories.index') }}">Categories</a>
                            <a class="cs-nav-link {{ request()->routeIs('cool.brands.*') ? 'is-active' : '' }}" href="{{ route('cool.brands.index') }}">Brands</a>
                            <a class="cs-nav-link {{ request()->routeIs('cool.units.*') ? 'is-active' : '' }}" href="{{ route('cool.units.index') }}">Units</a>
                        @endif
                        <a class="cs-nav-link {{ request()->routeIs('cool.stock.*') ? 'is-active' : '' }}" href="{{ route('cool.stock.index') }}">Stock</a>
                        @if($isManager)
                            <a class="cs-nav-link {{ request()->routeIs('cool.purchases.*') ? 'is-active' : '' }}" href="{{ route('cool.purchases.index') }}">Purchases</a>
                        @endif
                    </div>
                </div>

                <div>
                    <button
                        type="button"
                        class="cs-nav-link w-full justify-between"
                        :class="creditOpen ? 'is-active' : ''"
                        @click.stop="creditOpen = !creditOpen"
                    >
                        <span>Credit</span>
                        <span class="text-xs" x-text="creditOpen ? '▾' : '▸'"></span>
                    </button>
                    <div class="cs-nav-sub" x-show="creditOpen" x-cloak>
                        <a class="cs-nav-link {{ request()->routeIs('cool.credit.customers.*') ? 'is-active' : '' }}" href="{{ route('cool.credit.customers.index') }}">Customers</a>
                        @if($isManager)
                            <a class="cs-nav-link {{ request()->routeIs('cool.credit.suppliers.*') ? 'is-active' : '' }}" href="{{ route('cool.credit.suppliers.index') }}">Suppliers</a>
                        @endif
                    </div>
                </div>

                <a class="cs-nav-link {{ request()->routeIs('cool.customers.*') && ! request()->routeIs('cool.credit.*') ? 'is-active' : '' }}" href="{{ route('cool.customers.index') }}">Customers</a>
                @if($isManager)
                    <a class="cs-nav-link {{ request()->routeIs('cool.suppliers.*') && ! request()->routeIs('cool.credit.*') ? 'is-active' : '' }}" href="{{ route('cool.suppliers.index') }}">Suppliers</a>
                    <a class="cs-nav-link {{ request()->routeIs('cool.users.*') ? 'is-active' : '' }}" href="{{ route('cool.users.index') }}">Staff</a>
                    <a class="cs-nav-link {{ request()->routeIs('cool.settings.*') ? 'is-active' : '' }}" href="{{ route('cool.settings.edit') }}">Settings</a>
                @endif
            </nav>

            <div class="cs-panel p-3">
                <div class="text-sm font-semibold">{{ auth()->user()->name }}</div>
                <div class="text-xs text-[var(--cs-muted)] capitalize">{{ auth()->user()->roleName() }} · {{ auth()->user()->login_id }}</div>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="cs-btn-ghost w-full">Sign out</button>
                </form>
            </div>
        </aside>

        <div class="cs-main">
            <header class="cs-topbar">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" class="cs-btn-ghost px-3" @click="toggleSidebar()" aria-label="Toggle sidebar">
                        <span x-text="sidebarOpen ? '☰' : '☰'"></span>
                    </button>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--cs-muted)]">Cool Stationary</div>
                        <h1 class="brand-font text-2xl font-bold truncate">@yield('heading', 'Dashboard')</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @yield('actions')
                    @if($canUsePos)
                        <a href="{{ route('cool.pos.index') }}" class="cs-btn-primary">Open POS</a>
                    @endif
                </div>
            </header>

            <main class="p-4 sm:p-6 space-y-6">
                @if (session('success'))
                    <div class="cs-panel border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="cs-panel border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
