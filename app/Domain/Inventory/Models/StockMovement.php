<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Concerns\PosModel;
use App\Domain\Identity\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends PosModel
{
    public const UPDATED_AT = null;

    protected $hidden = [
        'unit_cost',
    ];

    protected $casts = [
        'qty_delta' => 'decimal:3',
        'qty_after' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(StockLot::class, 'lot_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
