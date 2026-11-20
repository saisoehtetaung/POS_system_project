<?php

namespace App\Domain\Analytics\Services;

use App\Domain\Customers\Models\Customer;
use App\Domain\Customers\Models\CustomerLedger;
use App\Domain\Inventory\Models\StockBalance;
use App\Domain\Purchasing\Models\Purchase;
use App\Domain\Purchasing\Models\Supplier;
use App\Domain\Purchasing\Models\SupplierPayment;
use App\Domain\Sales\Models\Payment;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Models\SaleItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    /**
     * @return array{kpis: array, charts: array, tables: array}
     */
    public function build(string $shopId, Carbon $from, Carbon $to, bool $canViewCost): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();

        $salesTotal = $this->periodSalesTotal($shopId, $from, $to);
        $netSales = $this->periodNetSales($shopId, $from, $to);
        $cogs = $canViewCost ? $this->periodCogs($shopId, $from, $to) : null;
        $grossProfit = $canViewCost ? round($netSales - $cogs, 2) : null;
        $marginPercent = ($canViewCost && $netSales > 0)
            ? round(($grossProfit / $netSales) * 100, 1)
            : null;

        $daily = $this->dailySalesSeries($shopId, $from, $to, $canViewCost);
        $cashSeries = $this->dailyCashSeries($shopId, $from, $to);
        $arSeries = $this->dailyArSeries($shopId, $from, $to);

        return [
            'kpis' => [
                'sales_total' => $salesTotal,
                'gross_profit' => $grossProfit,
                'margin_percent' => $marginPercent,
                'ar_outstanding' => $this->arOutstanding($shopId),
                'ap_outstanding' => $this->apOutstanding($shopId),
                'cash_in' => $this->periodCashIn($shopId, $from, $to),
                'low_stock' => $this->lowStockCount($shopId),
            ],
            'charts' => [
                'salesTrend' => [
                    'labels' => $daily['labels'],
                    'sales' => $daily['sales'],
                    'profit' => $daily['profit'],
                    'canViewCost' => $canViewCost,
                ],
                'salesByCategory' => $this->salesByCategory($shopId, $from, $to),
                'cashFlow' => [
                    'labels' => $cashSeries['labels'],
                    'cashIn' => $cashSeries['cashIn'],
                    'cashOut' => $cashSeries['cashOut'],
                ],
                'arFlow' => [
                    'labels' => $arSeries['labels'],
                    'issued' => $arSeries['issued'],
                    'repaid' => $arSeries['repaid'],
                ],
                'apAging' => $this->apAging($shopId),
            ],
            'tables' => [
                'topProducts' => $this->topProducts($shopId, $from, $to),
                'topDebtors' => $this->topDebtors($shopId),
                'recentSales' => $this->recentSales($shopId),
            ],
        ];
    }

    private function periodSalesTotal(string $shopId, Carbon $from, Carbon $to): float
    {
        return (float) Sale::query()
            ->where('shop_id', $shopId)
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$from, $to])
            ->sum('total');
    }

    private function periodNetSales(string $shopId, Carbon $from, Carbon $to): float
    {
        return (float) Sale::query()
            ->where('shop_id', $shopId)
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw('COALESCE(SUM(subtotal - discount_amount), 0) as net')
            ->value('net');
    }

    private function periodCogs(string $shopId, Carbon $from, Carbon $to): float
    {
        $saleIds = Sale::query()
            ->where('shop_id', $shopId)
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$from, $to])
            ->pluck('id');

        if ($saleIds->isEmpty()) {
            return 0.0;
        }

        $movementCogs = (float) DB::table('stock_movements')
            ->where('shop_id', $shopId)
            ->whereIn('movement_type', ['sale_out', 'bundle_component_out'])
            ->where('reference_type', Sale::class)
            ->whereIn('reference_id', $saleIds)
            ->selectRaw('COALESCE(SUM(ABS(qty_delta) * COALESCE(unit_cost, 0)), 0) as cogs')
            ->value('cogs');

        $salesWithMovements = DB::table('stock_movements')
            ->where('shop_id', $shopId)
            ->whereIn('movement_type', ['sale_out', 'bundle_component_out'])
            ->where('reference_type', Sale::class)
            ->whereIn('reference_id', $saleIds)
            ->distinct()
            ->pluck('reference_id');

        $fallbackSaleIds = $saleIds->diff($salesWithMovements);

        $fallbackCogs = 0.0;
        if ($fallbackSaleIds->isNotEmpty()) {
            $fallbackCogs = (float) SaleItem::query()
                ->whereIn('sale_id', $fallbackSaleIds)
                ->leftJoin('product_variants', 'product_variants.id', '=', 'sale_items.variant_id')
                ->selectRaw('COALESCE(SUM(sale_items.quantity_base * COALESCE(product_variants.cost_price, 0)), 0) as cogs')
                ->value('cogs');
        }

        // Untracked lines on sales that already have some stock movements (mixed carts)
        $untrackedCogs = 0.0;
        if ($salesWithMovements->isNotEmpty()) {
            $untrackedCogs = (float) SaleItem::query()
                ->whereIn('sale_items.sale_id', $salesWithMovements)
                ->join('products', 'products.id', '=', 'sale_items.product_id')
                ->leftJoin('product_variants', 'product_variants.id', '=', 'sale_items.variant_id')
                ->where('products.track_stock', false)
                ->selectRaw('COALESCE(SUM(sale_items.quantity_base * COALESCE(product_variants.cost_price, 0)), 0) as cogs')
                ->value('cogs');
        }

        return round($movementCogs + $fallbackCogs + $untrackedCogs, 2);
    }

    /**
     * @return array{labels: list<string>, sales: list<float>, profit: list<float|null>}
     */
    private function dailySalesSeries(string $shopId, Carbon $from, Carbon $to, bool $canViewCost): array
    {
        $labels = $this->dateLabels($from, $to);

        $salesRows = Sale::query()
            ->where('shop_id', $shopId)
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw('DATE(sold_at) as day')
            ->selectRaw('SUM(total) as sales_total')
            ->selectRaw('SUM(subtotal - discount_amount) as net_sales')
            ->groupBy('day')
            ->pluck('sales_total', 'day')
            ->map(fn ($v) => (float) $v);

        $netRows = Sale::query()
            ->where('shop_id', $shopId)
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw('DATE(sold_at) as day')
            ->selectRaw('SUM(subtotal - discount_amount) as net_sales')
            ->groupBy('day')
            ->pluck('net_sales', 'day')
            ->map(fn ($v) => (float) $v);

        $cogsByDay = collect();
        if ($canViewCost) {
            $cogsByDay = $this->dailyCogsMap($shopId, $from, $to);
        }

        $sales = [];
        $profit = [];
        foreach ($labels as $label) {
            $key = $label;
            $daySales = (float) ($salesRows[$key] ?? 0);
            $sales[] = round($daySales, 2);
            if ($canViewCost) {
                $dayNet = (float) ($netRows[$key] ?? 0);
                $dayCogs = (float) ($cogsByDay[$key] ?? 0);
                $profit[] = round($dayNet - $dayCogs, 2);
            } else {
                $profit[] = null;
            }
        }

        return [
            'labels' => $labels,
            'sales' => $sales,
            'profit' => $profit,
        ];
    }

    private function dailyCogsMap(string $shopId, Carbon $from, Carbon $to): Collection
    {
        $saleIdsByDay = Sale::query()
            ->where('shop_id', $shopId)
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw('id, DATE(sold_at) as day')
            ->get()
            ->groupBy('day');

        $map = collect();
        foreach ($saleIdsByDay as $day => $sales) {
            $ids = $sales->pluck('id');
            $map[$day] = $this->cogsForSaleIds($shopId, $ids);
        }

        return $map;
    }

    private function cogsForSaleIds(string $shopId, Collection $saleIds): float
    {
        if ($saleIds->isEmpty()) {
            return 0.0;
        }

        $movementCogs = (float) DB::table('stock_movements')
            ->where('shop_id', $shopId)
            ->whereIn('movement_type', ['sale_out', 'bundle_component_out'])
            ->where('reference_type', Sale::class)
            ->whereIn('reference_id', $saleIds)
            ->selectRaw('COALESCE(SUM(ABS(qty_delta) * COALESCE(unit_cost, 0)), 0) as cogs')
            ->value('cogs');

        $salesWithMovements = DB::table('stock_movements')
            ->where('shop_id', $shopId)
            ->whereIn('movement_type', ['sale_out', 'bundle_component_out'])
            ->where('reference_type', Sale::class)
            ->whereIn('reference_id', $saleIds)
            ->distinct()
            ->pluck('reference_id');

        $fallbackSaleIds = $saleIds->diff($salesWithMovements);
        $fallbackCogs = 0.0;
        if ($fallbackSaleIds->isNotEmpty()) {
            $fallbackCogs = (float) SaleItem::query()
                ->whereIn('sale_id', $fallbackSaleIds)
                ->leftJoin('product_variants', 'product_variants.id', '=', 'sale_items.variant_id')
                ->selectRaw('COALESCE(SUM(sale_items.quantity_base * COALESCE(product_variants.cost_price, 0)), 0) as cogs')
                ->value('cogs');
        }

        $untrackedCogs = 0.0;
        if ($salesWithMovements->isNotEmpty()) {
            $untrackedCogs = (float) SaleItem::query()
                ->whereIn('sale_items.sale_id', $salesWithMovements)
                ->join('products', 'products.id', '=', 'sale_items.product_id')
                ->leftJoin('product_variants', 'product_variants.id', '=', 'sale_items.variant_id')
                ->where('products.track_stock', false)
                ->selectRaw('COALESCE(SUM(sale_items.quantity_base * COALESCE(product_variants.cost_price, 0)), 0) as cogs')
                ->value('cogs');
        }

        return round($movementCogs + $fallbackCogs + $untrackedCogs, 2);
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function salesByCategory(string $shopId, Carbon $from, Carbon $to): array
    {
        $rows = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('sales.shop_id', $shopId)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$from, $to])
            ->selectRaw("COALESCE(categories.name, 'Uncategorized') as category_name")
            ->selectRaw('SUM(sale_items.line_total) as revenue')
            ->groupBy('category_name')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('category_name')->all(),
            'values' => $rows->pluck('revenue')->map(fn ($v) => round((float) $v, 2))->all(),
        ];
    }

    /**
     * @return array{labels: list<string>, cashIn: list<float>, cashOut: list<float>}
     */
    private function dailyCashSeries(string $shopId, Carbon $from, Carbon $to): array
    {
        $labels = $this->dateLabels($from, $to);

        $cashInRows = Payment::query()
            ->where('method', '!=', 'credit')
            ->whereBetween('paid_at', [$from, $to])
            ->where(function ($query) use ($shopId) {
                $query->whereHas('sale', fn ($q) => $q->where('shop_id', $shopId))
                    ->orWhereHas('customer', fn ($q) => $q->where('shop_id', $shopId));
            })
            ->selectRaw('DATE(paid_at) as day')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn ($v) => (float) $v);

        $cashOutRows = SupplierPayment::query()
            ->whereHas('supplier', fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('DATE(paid_at) as day')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn ($v) => (float) $v);

        $cashIn = [];
        $cashOut = [];
        foreach ($labels as $label) {
            $cashIn[] = round((float) ($cashInRows[$label] ?? 0), 2);
            $cashOut[] = round((float) ($cashOutRows[$label] ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'cashIn' => $cashIn,
            'cashOut' => $cashOut,
        ];
    }

    /**
     * @return array{labels: list<string>, issued: list<float>, repaid: list<float>}
     */
    private function dailyArSeries(string $shopId, Carbon $from, Carbon $to): array
    {
        $labels = $this->dateLabels($from, $to);

        $issuedRows = CustomerLedger::query()
            ->whereHas('customer', fn ($q) => $q->where('shop_id', $shopId))
            ->where('entry_type', 'credit_sale')
            ->whereBetween('occurred_at', [$from, $to])
            ->selectRaw('DATE(occurred_at) as day')
            ->selectRaw('SUM(debit) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn ($v) => (float) $v);

        $repaidRows = CustomerLedger::query()
            ->whereHas('customer', fn ($q) => $q->where('shop_id', $shopId))
            ->where('entry_type', 'repayment')
            ->whereBetween('occurred_at', [$from, $to])
            ->selectRaw('DATE(occurred_at) as day')
            ->selectRaw('SUM(credit) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn ($v) => (float) $v);

        $issued = [];
        $repaid = [];
        foreach ($labels as $label) {
            $issued[] = round((float) ($issuedRows[$label] ?? 0), 2);
            $repaid[] = round((float) ($repaidRows[$label] ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'issued' => $issued,
            'repaid' => $repaid,
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    private function apAging(string $shopId): array
    {
        $today = today();
        $buckets = [
            'Overdue' => 0.0,
            '0-7 days' => 0.0,
            '8-30 days' => 0.0,
            '30+ days' => 0.0,
        ];

        $purchases = Purchase::query()
            ->where('shop_id', $shopId)
            ->where('payment_type', 'credit')
            ->where('status', 'received')
            ->whereColumn('amount_paid', '<', 'total')
            ->get(['total', 'amount_paid', 'due_date', 'purchased_at']);

        foreach ($purchases as $purchase) {
            $due = max(0, (float) $purchase->total - (float) $purchase->amount_paid);
            $dueDate = ($purchase->due_date ?? $purchase->purchased_at)?->copy()->startOfDay();
            if (! $dueDate) {
                $buckets['Overdue'] += $due;
                continue;
            }

            if ($dueDate->lt($today)) {
                $buckets['Overdue'] += $due;
            } else {
                $days = $today->diffInDays($dueDate);
                if ($days <= 7) {
                    $buckets['0-7 days'] += $due;
                } elseif ($days <= 30) {
                    $buckets['8-30 days'] += $due;
                } else {
                    $buckets['30+ days'] += $due;
                }
            }
        }

        $opening = (float) Supplier::query()
            ->where('shop_id', $shopId)
            ->where('opening_balance', '>', 0)
            ->sum('opening_balance');
        $buckets['Overdue'] += $opening;

        return [
            'labels' => array_keys($buckets),
            'values' => array_map(fn ($v) => round($v, 2), array_values($buckets)),
        ];
    }

    private function arOutstanding(string $shopId): float
    {
        return (float) Customer::query()
            ->where('shop_id', $shopId)
            ->where('balance', '>', 0)
            ->sum('balance');
    }

    private function apOutstanding(string $shopId): float
    {
        $purchaseDue = (float) Purchase::query()
            ->where('shop_id', $shopId)
            ->where('payment_type', 'credit')
            ->where('status', 'received')
            ->whereColumn('amount_paid', '<', 'total')
            ->selectRaw('COALESCE(SUM(GREATEST(total - amount_paid, 0)), 0) as due')
            ->value('due');

        $opening = (float) Supplier::query()
            ->where('shop_id', $shopId)
            ->where('opening_balance', '>', 0)
            ->sum('opening_balance');

        return round($purchaseDue + $opening, 2);
    }

    private function periodCashIn(string $shopId, Carbon $from, Carbon $to): float
    {
        return (float) Payment::query()
            ->where('method', '!=', 'credit')
            ->whereBetween('paid_at', [$from, $to])
            ->where(function ($query) use ($shopId) {
                $query->whereHas('sale', fn ($q) => $q->where('shop_id', $shopId))
                    ->orWhereHas('customer', fn ($q) => $q->where('shop_id', $shopId));
            })
            ->sum('amount');
    }

    private function lowStockCount(string $shopId): int
    {
        return (int) StockBalance::query()
            ->where('shop_id', $shopId)
            ->whereColumn(
                'qty_on_hand',
                '<=',
                DB::raw('(select reorder_level from product_variants where product_variants.id = stock_balances.variant_id)')
            )
            ->count();
    }

    /**
     * @return list<array{name: string, revenue: float, qty: float}>
     */
    private function topProducts(string $shopId, Carbon $from, Carbon $to): array
    {
        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.shop_id', $shopId)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$from, $to])
            ->selectRaw('products.name as name')
            ->selectRaw('SUM(sale_items.line_total) as revenue')
            ->selectRaw('SUM(sale_items.quantity_base) as qty')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'revenue' => round((float) $row->revenue, 2),
                'qty' => round((float) $row->qty, 3),
            ])
            ->all();
    }

    /**
     * @return list<array{name: string, balance: float, credit_limit: float}>
     */
    private function topDebtors(string $shopId): array
    {
        return Customer::query()
            ->where('shop_id', $shopId)
            ->where('balance', '>', 0)
            ->orderByDesc('balance')
            ->limit(10)
            ->get(['name', 'balance', 'credit_limit'])
            ->map(fn (Customer $c) => [
                'name' => $c->name,
                'balance' => round((float) $c->balance, 2),
                'credit_limit' => round((float) $c->credit_limit, 2),
            ])
            ->all();
    }

    /**
     * @return list<array{sale_no: string, cashier: ?string, customer: string, total: float, sold_at: string}>
     */
    private function recentSales(string $shopId): array
    {
        return Sale::query()
            ->with(['cashier:id,name', 'customer:id,name'])
            ->where('shop_id', $shopId)
            ->where('status', 'completed')
            ->latest('sold_at')
            ->limit(8)
            ->get()
            ->map(fn (Sale $sale) => [
                'sale_no' => $sale->sale_no,
                'cashier' => optional($sale->cashier)->name,
                'customer' => optional($sale->customer)->name ?? 'Walk-in',
                'total' => round((float) $sale->total, 2),
                'sold_at' => optional($sale->sold_at)?->format('d M Y H:i') ?? '',
            ])
            ->all();
    }

    /**
     * @return list<string>
     */
    private function dateLabels(Carbon $from, Carbon $to): array
    {
        $period = CarbonPeriod::create($from->copy()->startOfDay(), '1 day', $to->copy()->startOfDay());
        $labels = [];
        foreach ($period as $date) {
            $labels[] = $date->toDateString();
        }

        return $labels;
    }
}
