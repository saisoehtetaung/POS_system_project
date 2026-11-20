<?php

namespace App\Domain\Shifts\Models;

use App\Domain\Concerns\PosModel;
use App\Domain\Identity\Models\Shop;
use App\Domain\Identity\Models\Terminal;
use App\Domain\Sales\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class Shift extends PosModel
{
    protected $guarded = ['id', 'open_slot'];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $shift): void {
            if ($shift->status !== 'open') {
                return;
            }

            $open = static::query()
                ->where('terminal_id', $shift->terminal_id)
                ->where('status', 'open')
                ->when($shift->exists, function ($query) use ($shift) {
                    $query->whereKeyNot($shift->getKey());
                })
                ->exists();

            if ($open) {
                throw new RuntimeException('This terminal already has an open shift.');
            }
        });
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
