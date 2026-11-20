<?php

namespace App\Domain\Identity\Models;

use App\Domain\Concerns\PosModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Terminal extends PosModel
{
    protected $casts = [
        'drawer_enabled' => 'boolean',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
