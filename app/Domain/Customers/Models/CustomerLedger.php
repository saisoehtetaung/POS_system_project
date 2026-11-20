<?php

namespace App\Domain\Customers\Models;

use App\Domain\Concerns\PosModel;
use App\Domain\Sales\Models\Payment;
use App\Domain\Sales\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerLedger extends PosModel
{
    public const UPDATED_AT = null;

    protected $table = 'customer_ledger';

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'due_date' => 'date',
        'occurred_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
