<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Customers\Models\Customer;
use App\Domain\Purchasing\Models\Purchase;
use App\Domain\Purchasing\Models\Supplier;
use App\Domain\Purchasing\Models\SupplierPayment;
use App\Domain\Sales\Models\Sale;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    public function customers()
    {
        $shopId = auth()->user()->shop_id;

        $customers = Customer::query()
            ->where('shop_id', $shopId)
            ->where('balance', '>', 0)
            ->orderByDesc('balance')
            ->paginate(20);

        $lastSaleDates = Sale::query()
            ->select('customer_id', DB::raw('MAX(sold_at) as last_credit_at'))
            ->where('shop_id', $shopId)
            ->where('amount_due', '>', 0)
            ->whereIn('customer_id', $customers->pluck('id'))
            ->groupBy('customer_id')
            ->pluck('last_credit_at', 'customer_id');

        return view('cool.credit.customers.index', compact('customers', 'lastSaleDates'));
    }

    public function showCustomer(string $id)
    {
        $customer = Customer::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->whereKey($id)
            ->firstOrFail();

        $openSales = Sale::query()
            ->where('customer_id', $customer->id)
            ->where('amount_due', '>', 0)
            ->whereIn('status', ['completed', 'refunded'])
            ->orderByDesc('sold_at')
            ->get();

        $ledger = $customer->ledgerEntries()
            ->latest('occurred_at')
            ->paginate(20);

        return view('cool.credit.customers.show', compact('customer', 'openSales', 'ledger'));
    }

    public function suppliers()
    {
        $shopId = auth()->user()->shop_id;

        $purchaseDue = Purchase::query()
            ->select(
                'supplier_id',
                DB::raw('SUM(GREATEST(total - amount_paid, 0)) as purchase_due'),
                DB::raw('MIN(COALESCE(due_date, purchased_at)) as oldest_due_at')
            )
            ->where('shop_id', $shopId)
            ->where('payment_type', 'credit')
            ->where('status', 'received')
            ->whereColumn('amount_paid', '<', 'total')
            ->groupBy('supplier_id')
            ->get()
            ->keyBy('supplier_id');

        $suppliers = Supplier::query()
            ->where('shop_id', $shopId)
            ->where(function ($query) use ($purchaseDue) {
                $query->where('opening_balance', '>', 0);
                if ($purchaseDue->isNotEmpty()) {
                    $query->orWhereIn('id', $purchaseDue->keys());
                }
            })
            ->orderBy('name')
            ->get()
            ->map(function (Supplier $supplier) use ($purchaseDue) {
                $row = $purchaseDue->get($supplier->id);
                $due = (float) ($row->purchase_due ?? 0) + max(0, (float) $supplier->opening_balance);

                $supplier->setAttribute('payable_due', $due);
                $supplier->setAttribute('oldest_due_at', $row->oldest_due_at ?? null);

                return $supplier;
            })
            ->filter(fn (Supplier $supplier) => (float) $supplier->payable_due > 0)
            ->values();

        return view('cool.credit.suppliers.index', compact('suppliers'));
    }

    public function showSupplier(string $id)
    {
        $supplier = Supplier::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->whereKey($id)
            ->firstOrFail();

        $openPurchases = Purchase::query()
            ->where('supplier_id', $supplier->id)
            ->where('payment_type', 'credit')
            ->where('status', 'received')
            ->whereColumn('amount_paid', '<', 'total')
            ->orderBy('due_date')
            ->orderBy('purchased_at')
            ->get();

        $purchaseDue = $openPurchases->sum(
            fn (Purchase $purchase) => max(0, (float) $purchase->total - (float) $purchase->amount_paid)
        );
        $due = $purchaseDue + max(0, (float) $supplier->opening_balance);

        $payments = SupplierPayment::query()
            ->where('supplier_id', $supplier->id)
            ->latest('paid_at')
            ->paginate(15);

        return view('cool.credit.suppliers.show', compact('supplier', 'openPurchases', 'payments', 'due'));
    }
}
