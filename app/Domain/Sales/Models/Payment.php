<?php

namespace App\Domain\Sales\Models;

use App\Domain\Concerns\PosModel;
use App\Domain\Customers\Models\Customer;
use App\Domain\Shifts\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends PosModel
{
    public const UPDATED_AT = null;

    protected $casts = [
        'amount' => 'decimal:2',
        'tendered' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
