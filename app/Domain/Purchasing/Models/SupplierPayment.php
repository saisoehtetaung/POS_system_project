<?php

namespace App\Domain\Purchasing\Models;

use App\Domain\Concerns\PosModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends PosModel
{
    public const UPDATED_AT = null;

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
