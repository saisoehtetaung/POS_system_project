<?php

namespace App\Http\Controllers\Cool;

use App\Domain\Customers\Models\Customer;
use App\Domain\Customers\Models\CustomerLedger;
use App\Domain\Sales\Models\Payment;
use App\Domain\Shifts\Models\Shift;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->orderBy('name')
            ->paginate(15);

        return view('cool.people.customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $this->save($request);

        return redirect()->route('cool.customers.index')->with('success', 'Customer added.');
    }

    public function show(string $id)
    {
        $customer = $this->shopCustomer($id);
        $ledger = $customer->ledgerEntries()->latest('occurred_at')->paginate(20);

        return view('cool.people.customers.show', compact('customer', 'ledger'));
    }

    public function update(Request $request, string $id)
    {
        $customer = $this->shopCustomer($id);
        $this->save($request, $customer);

        return redirect()->route('cool.customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(string $id)
    {
        $customer = $this->shopCustomer($id);
        $customer->update(['is_active' => false]);
        $customer->delete();

        return redirect()->route('cool.customers.index')->with('success', 'Customer deleted.');
    }

    public function repay(Request $request, string $id)
    {
        $customer = $this->shopCustomer($id);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(['cash', 'card', 'transfer'])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = (float) $data['amount'];
        if ($amount > (float) $customer->balance) {
            throw ValidationException::withMessages([
                'amount' => 'Repayment cannot exceed outstanding balance.',
            ]);
        }

        $shift = Shift::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if (! $shift) {
            throw ValidationException::withMessages([
                'amount' => 'Open a shift before recording repayments.',
            ]);
        }

        DB::transaction(function () use ($customer, $data, $amount, $shift) {
            $payment = Payment::query()->create([
                'sale_id' => null,
                'customer_id' => $customer->id,
                'shift_id' => $shift->id,
                'method' => $data['method'],
                'amount' => $amount,
                'paid_at' => now(),
                'created_by' => auth()->id(),
            ]);

            $customer->balance = max(0, (float) $customer->balance - $amount);
            $customer->save();

            CustomerLedger::query()->create([
                'customer_id' => $customer->id,
                'sale_id' => null,
                'payment_id' => $payment->id,
                'entry_type' => 'repayment',
                'debit' => 0,
                'credit' => $amount,
                'balance_after' => $customer->balance,
                'occurred_at' => now(),
                'created_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Repayment recorded.');
    }

    private function save(Request $request, ?Customer $customer = null): Customer
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'customer_type' => ['required', Rule::in(['regular', 'b2b'])],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'shop_id' => auth()->user()->shop_id,
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'customer_type' => $data['customer_type'],
            'credit_limit' => $data['credit_limit'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if ($customer) {
            $customer->update($payload);

            return $customer;
        }

        $payload['balance'] = 0;

        return Customer::query()->create($payload);
    }

    private function shopCustomer(string $id): Customer
    {
        return Customer::query()
            ->where('shop_id', auth()->user()->shop_id)
            ->whereKey($id)
            ->firstOrFail();
    }
}
