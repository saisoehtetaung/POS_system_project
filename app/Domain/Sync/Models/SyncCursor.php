<?php

namespace App\Domain\Sync\Models;

use App\Domain\Concerns\PosModel;
use App\Domain\Identity\Models\Terminal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncCursor extends PosModel
{
    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }
}
