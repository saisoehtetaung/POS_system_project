<?php

namespace App\Domain\Sync\Models;

use App\Domain\Identity\Models\Terminal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncProcessedKey extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'idempotency_key';

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }
}
