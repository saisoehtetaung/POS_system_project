<?php

namespace App\Domain\Purchasing\Models;

use App\Domain\Concerns\PosModel;
use App\Domain\Identity\Models\Shop;
use App\Domain\Identity\Models\Terminal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends PosModel
{
    use SoftDeletes;

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function originTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origin_terminal_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(SupplierPriceHistory::class);
    }
}
