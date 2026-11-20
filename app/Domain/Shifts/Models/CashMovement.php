<?php

namespace App\Domain\Shifts\Models;

use App\Domain\Concerns\PosModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends PosModel
{
    public const UPDATED_AT = null;

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
