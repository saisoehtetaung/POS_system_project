<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Purchasing\Models\Purchase;
use App\Domain\Purchasing\Models\Supplier;
use App\Domain\Purchasing\Models\SupplierPayment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->orderBy('name')
            ->paginate(15);

        return view('cool.people.suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $this->save($request);

        return redirect()->route('cool.suppliers.index')->with('success', 'Supplier added.');
    }

    public function show(string $id)
    {
        $supplier = $this->shopSupplier($id);
        $purchases = Purchase::query()
            ->where('supplier_id', $supplier->id)
            ->latest('purchased_at')
            ->paginate(10, ['*'], 'purchases_page');
        $payments = SupplierPayment::query()
            ->where('supplier_id', $supplier->id)
            ->latest('paid_at')
            ->paginate(10, ['*'], 'payments_page');

        $due = Purchase::query()
            ->where('supplier_id', $supplier->id)
            ->where('payment_type', 'credit')
            ->where('status', 'received')
            ->get()
            ->sum(fn (Purchase $p) => max(0, (float) $p->total - (float) $p->amount_paid));

        $openPurchases = Purchase::query()
            ->where('supplier_id', $supplier->id)
            ->where('payment_type', 'credit')
            ->where('status', 'received')
            ->whereColumn('amount_paid', '<', 'total')
            ->orderBy('due_date')
            ->get();

        return view('cool.people.suppliers.show', compact('supplier', 'purchases', 'payments', 'due', 'openPurchases'));
    }

    public function update(Request $request, string $id)
    {
        $supplier = $this->shopSupplier($id);
        $this->save($request, $supplier);

        return redirect()->route('cool.suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(string $id)
    {
        $supplier = $this->shopSupplier($id);
        $supplier->update(['is_active' => false]);
        $supplier->delete();

        return redirect()->route('cool.suppliers.index')->with('success', 'Supplier deleted.');
    }

    public function pay(Request $request, string $id)
    {
        $supplier = $this->shopSupplier($id);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'max:50'],
            'purchase_id' => [
                'nullable',
                Rule::exists('purchases', 'id')->where('supplier_id', $supplier->id),
            ],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($supplier, $data) {
            $amount = (float) $data['amount'];
            $purchase = null;

            if (! empty($data['purchase_id'])) {
                $purchase = Purchase::query()
                    ->where('supplier_id', $supplier->id)
                    ->whereKey($data['purchase_id'])
                    ->firstOrFail();

                $remaining = max(0, (float) $purchase->total - (float) $purchase->amount_paid);
                if ($amount > $remaining) {
                    throw ValidationException::withMessages([
                        'amount' => 'Amount exceeds remaining due on this purchase.',
                    ]);
                }

                $purchase->amount_paid = (float) $purchase->amount_paid + $amount;
                $purchase->save();
            }

            SupplierPayment::query()->create([
                'supplier_id' => $supplier->id,
                'purchase_id' => $purchase?->id,
                'amount' => $amount,
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'paid_at' => now(),
                'created_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Supplier payment recorded.');
    }

    private function save(Request $request, ?Supplier $supplier = null): Supplier
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'opening_balance' => ['nullable', 'numeric'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'shop_id' => auth()->user()->shop_id,
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'opening_balance' => $data['opening_balance'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if ($supplier) {
            $supplier->update($payload);

            return $supplier;
        }

        return Supplier::query()->create($payload);
    }

    private function shopSupplier(string $id): Supplier
    {
        return Supplier::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->whereKey($id)
            ->firstOrFail();
    }
}
