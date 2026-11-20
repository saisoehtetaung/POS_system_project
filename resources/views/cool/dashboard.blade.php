@extends('cool.layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    @php
        $presets = [
            'today' => 'Today',
            '7d' => '7 days',
            '30d' => '30 days',
            'month' => 'This month',
            'custom' => 'Custom',
        ];
    @endphp

    <div
        class="space-y-6"
        x-data="dashboardCharts(@js($charts))"
    >
        <form method="get" action="{{ route('cool.dashboard') }}" class="cs-panel p-4 sm:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--cs-muted)]">Period</div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($presets as $key => $label)
                            <label class="inline-flex cursor-pointer items-center">
                                <input
                                    type="radio"
                                    name="preset"
                                    value="{{ $key }}"
                                    class="peer sr-only"
                                    @checked($range['preset'] === $key)
                                    onchange="if (this.value !== 'custom') this.form.submit()"
                                >
                                <span class="rounded-xl border border-[var(--cs-line)] px-3 py-1.5 text-sm font-semibold text-[var(--cs-ink)] peer-checked:border-[var(--cs-accent)] peer-checked:bg-[var(--cs-sidebar-active)] peer-checked:text-[var(--cs-accent-deep)]">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-wrap items-end gap-3" x-data="{ custom: @js($range['preset'] === 'custom') }">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--cs-muted)]">From</label>
                        <input class="cs-input mt-1 w-40" type="date" name="from" value="{{ $range['from'] }}" @focus="custom = true">
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--cs-muted)]">To</label>
                        <input class="cs-input mt-1 w-40" type="date" name="to" value="{{ $range['to'] }}" @focus="custom = true">
                    </div>
                    <button type="submit" class="cs-btn-primary" @click="if (custom) { $el.form.preset.value = 'custom' }">Apply</button>
                </div>
            </div>
            <div class="mt-3 text-sm text-[var(--cs-muted)]">
                Showing {{ \Carbon\Carbon::parse($range['from'])->format('d M Y') }}
                – {{ \Carbon\Carbon::parse($range['to'])->format('d M Y') }}
            </div>
        </form>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="cs-stat">
                <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--cs-muted)]">Sales</div>
                <div class="brand-font mt-2 text-3xl font-bold">{{ number_format($kpis['sales_total'], 0) }}</div>
                <div class="mt-1 text-sm text-[var(--cs-muted)]">MMK · completed</div>
            </div>

            <div class="cs-stat">
                <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--cs-muted)]">Gross Profit</div>
                @if($canViewCost)
                    <div class="brand-font mt-2 text-3xl font-bold">{{ number_format($kpis['gross_profit'], 0) }}</div>
                    <div class="mt-1 text-sm text-[var(--cs-muted)]">{{ number_format($kpis['margin_percent'] ?? 0, 1) }}% margin</div>
                @else
                    <div class="brand-font mt-2 text-3xl font-bold text-[var(--cs-muted)]">—</div>
                    <div class="mt-1 text-sm text-[var(--cs-muted)]">Cost view required</div>
                @endif
            </div>

            <div class="cs-stat">
                <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--cs-muted)]">AR Outstanding</div>
                <div class="brand-font mt-2 text-3xl font-bold">{{ number_format($kpis['ar_outstanding'], 0) }}</div>
                <div class="mt-1 text-sm text-[var(--cs-muted)]">Customer credit due</div>
            </div>

            <div class="cs-stat">
                <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--cs-muted)]">AP Outstanding</div>
                <div class="brand-font mt-2 text-3xl font-bold">{{ number_format($kpis['ap_outstanding'], 0) }}</div>
                <div class="mt-1 text-sm text-[var(--cs-muted)]">Supplier payables</div>
            </div>

            <div class="cs-stat">
                <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--cs-muted)]">Cash In</div>
                <div class="brand-font mt-2 text-3xl font-bold">{{ number_format($kpis['cash_in'], 0) }}</div>
                <div class="mt-1 text-sm text-[var(--cs-muted)]">Cash / card / transfer</div>
            </div>

            <div class="cs-stat">
                <div class="text-xs font-semibold uppercase tracking-[0.14em] text-[var(--cs-muted)]">Low Stock</div>
                <div class="brand-font mt-2 text-3xl font-bold">{{ number_format($kpis['low_stock'], 0) }}</div>
                <div class="mt-1 text-sm text-[var(--cs-muted)]">At or below reorder</div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <section class="cs-panel p-5">
                <h2 class="brand-font text-lg font-bold">Sales &amp; profit trend</h2>
                <p class="mt-1 text-sm text-[var(--cs-muted)]">Daily completed sales{{ $canViewCost ? ' with gross profit' : '' }}</p>
                <div class="mt-4 h-72">
                    <canvas id="chart-sales-trend"></canvas>
                </div>
            </section>

            <section class="cs-panel p-5">
                <h2 class="brand-font text-lg font-bold">Sales by category</h2>
                <p class="mt-1 text-sm text-[var(--cs-muted)]">Top categories by line revenue</p>
                <div class="mt-4 h-72">
                    <canvas id="chart-category"></canvas>
                </div>
            </section>

            <section class="cs-panel p-5">
                <h2 class="brand-font text-lg font-bold">Cash in vs supplier payments</h2>
                <p class="mt-1 text-sm text-[var(--cs-muted)]">Trade cash flow for the period</p>
                <div class="mt-4 h-72">
                    <canvas id="chart-cash"></canvas>
                </div>
            </section>

            <section class="cs-panel p-5">
                <h2 class="brand-font text-lg font-bold">AR issued vs repayments</h2>
                <p class="mt-1 text-sm text-[var(--cs-muted)]">Customer credit sales and collections</p>
                <div class="mt-4 h-72">
                    <canvas id="chart-ar"></canvas>
                </div>
            </section>

            <section class="cs-panel p-5 xl:col-span-2">
                <h2 class="brand-font text-lg font-bold">AP aging</h2>
                <p class="mt-1 text-sm text-[var(--cs-muted)]">Open supplier credit by due bucket</p>
                <div class="mt-4 h-72 max-w-3xl">
                    <canvas id="chart-ap-aging"></canvas>
                </div>
            </section>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <section class="cs-panel overflow-hidden">
                <div class="flex items-center justify-between border-b border-[var(--cs-line)] px-5 py-4">
                    <h2 class="brand-font text-lg font-bold">Top products</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="cs-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty (base)</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tables['topProducts'] as $row)
                                <tr>
                                    <td class="font-semibold">{{ $row['name'] }}</td>
                                    <td>{{ number_format($row['qty'], 0) }}</td>
                                    <td>{{ number_format($row['revenue'], 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-10 text-center text-[var(--cs-muted)]">No sales in this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="cs-panel overflow-hidden">
                <div class="flex items-center justify-between border-b border-[var(--cs-line)] px-5 py-4">
                    <h2 class="brand-font text-lg font-bold">Top debtors</h2>
                    <a href="{{ route('cool.credit.customers.index') }}" class="text-sm font-semibold text-[var(--cs-accent-deep)]">View AR</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="cs-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Balance</th>
                                <th>Limit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tables['topDebtors'] as $row)
                                <tr>
                                    <td class="font-semibold">{{ $row['name'] }}</td>
                                    <td>{{ number_format($row['balance'], 0) }}</td>
                                    <td>{{ number_format($row['credit_limit'], 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-10 text-center text-[var(--cs-muted)]">No outstanding receivables.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <section class="cs-panel overflow-hidden">
            <div class="flex items-center justify-between border-b border-[var(--cs-line)] px-5 py-4">
                <h2 class="brand-font text-lg font-bold">Recent sales</h2>
                <a href="{{ route('cool.pos.index') }}" class="text-sm font-semibold text-[var(--cs-accent-deep)]">Go to POS</a>
            </div>
            <div class="overflow-x-auto">
                <table class="cs-table">
                    <thead>
                        <tr>
                            <th>Sale No</th>
                            <th>Cashier</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Sold At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tables['recentSales'] as $sale)
                            <tr>
                                <td class="font-semibold">{{ $sale['sale_no'] }}</td>
                                <td>{{ $sale['cashier'] }}</td>
                                <td>{{ $sale['customer'] }}</td>
                                <td>{{ number_format($sale['total'], 2) }}</td>
                                <td>{{ $sale['sold_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-[var(--cs-muted)]">
                                    No completed sales yet. Open POS Billing to start checkout.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        function dashboardCharts(payload) {
            const accent = '#fe9f43';
            const accentDeep = '#e8872a';
            const ink = '#1c2434';
            const muted = '#6b7285';
            const teal = '#0f766e';
            const rose = '#be123c';
            const sky = '#0369a1';

            const shortLabels = (labels) => labels.map((d) => {
                const parts = String(d).split('-');
                if (parts.length !== 3) return d;
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                return `${parseInt(parts[2], 10)} ${months[parseInt(parts[1], 10) - 1]}`;
            });

            const emptyPlugin = {
                id: 'emptyState',
                afterDraw(chart) {
                    const data = chart.data.datasets.flatMap((ds) => ds.data || []);
                    const hasData = data.some((v) => Number(v) > 0);
                    if (hasData) return;
                    const { ctx, chartArea } = chart;
                    if (!chartArea) return;
                    ctx.save();
                    ctx.fillStyle = muted;
                    ctx.font = '14px Manrope, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillText('No data for this period', (chartArea.left + chartArea.right) / 2, (chartArea.top + chartArea.bottom) / 2);
                    ctx.restore();
                },
            };

            return {
                instances: [],
                init() {
                    if (!window.Chart) return;
                    Chart.register(emptyPlugin);

                    const trend = payload.salesTrend || { labels: [], sales: [], profit: [], canViewCost: false };
                    const trendDatasets = [
                        {
                            type: 'bar',
                            label: 'Sales',
                            data: trend.sales || [],
                            backgroundColor: 'rgba(254, 159, 67, 0.55)',
                            borderRadius: 6,
                            order: 2,
                        },
                    ];
                    if (trend.canViewCost) {
                        trendDatasets.push({
                            type: 'line',
                            label: 'Gross profit',
                            data: trend.profit || [],
                            borderColor: teal,
                            backgroundColor: teal,
                            tension: 0.3,
                            order: 1,
                        });
                    }
                    this.instances.push(new Chart(document.getElementById('chart-sales-trend'), {
                        data: { labels: shortLabels(trend.labels || []), datasets: trendDatasets },
                        options: this.baseOptions(),
                    }));

                    const category = payload.salesByCategory || { labels: [], values: [] };
                    this.instances.push(new Chart(document.getElementById('chart-category'), {
                        type: 'doughnut',
                        data: {
                            labels: category.labels || [],
                            datasets: [{
                                data: category.values || [],
                                backgroundColor: ['#fe9f43', '#0f766e', '#0369a1', '#be123c', '#7c3aed', '#ca8a04', '#334155', '#db2777'],
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } },
                        },
                    }));

                    const cash = payload.cashFlow || { labels: [], cashIn: [], cashOut: [] };
                    this.instances.push(new Chart(document.getElementById('chart-cash'), {
                        type: 'bar',
                        data: {
                            labels: shortLabels(cash.labels || []),
                            datasets: [
                                { label: 'Cash in', data: cash.cashIn || [], backgroundColor: 'rgba(15, 118, 110, 0.7)', borderRadius: 6 },
                                { label: 'Supplier out', data: cash.cashOut || [], backgroundColor: 'rgba(190, 18, 60, 0.65)', borderRadius: 6 },
                            ],
                        },
                        options: this.baseOptions(),
                    }));

                    const ar = payload.arFlow || { labels: [], issued: [], repaid: [] };
                    this.instances.push(new Chart(document.getElementById('chart-ar'), {
                        type: 'bar',
                        data: {
                            labels: shortLabels(ar.labels || []),
                            datasets: [
                                { label: 'Issued', data: ar.issued || [], backgroundColor: 'rgba(3, 105, 161, 0.7)', borderRadius: 6 },
                                { label: 'Repaid', data: ar.repaid || [], backgroundColor: 'rgba(15, 118, 110, 0.7)', borderRadius: 6 },
                            ],
                        },
                        options: this.baseOptions(),
                    }));

                    const ap = payload.apAging || { labels: [], values: [] };
                    this.instances.push(new Chart(document.getElementById('chart-ap-aging'), {
                        type: 'bar',
                        data: {
                            labels: ap.labels || [],
                            datasets: [{
                                label: 'Amount due',
                                data: ap.values || [],
                                backgroundColor: [rose, accent, accentDeep, sky],
                                borderRadius: 6,
                            }],
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { beginAtZero: true, ticks: { color: muted }, grid: { color: '#eef1f6' } },
                                y: { ticks: { color: ink }, grid: { display: false } },
                            },
                        },
                    }));
                },
                baseOptions() {
                    return {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { color: ink } },
                        },
                        scales: {
                            x: { ticks: { color: muted, maxRotation: 0, autoSkip: true, maxTicksLimit: 10 }, grid: { display: false } },
                            y: { beginAtZero: true, ticks: { color: muted }, grid: { color: '#eef1f6' } },
                        },
                    };
                },
                destroy() {
                    this.instances.forEach((chart) => chart.destroy());
                    this.instances = [];
                },
            };
        }
    </script>
@endsection
