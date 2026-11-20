<?php

namespace App\Domain\Audit\Models;

use App\Domain\Concerns\PosModel;
use App\Domain\Identity\Models\Shop;
use App\Domain\Identity\Models\Terminal;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends PosModel
{
    public const UPDATED_AT = null;

    protected $casts = [
        'before_json' => 'array',
        'after_json' => 'array',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
