<?php

namespace App\Domain\Purchasing\Models;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Concerns\PosModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends PosModel
{
    protected $hidden = [
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'factor_to_base' => 'decimal:6',
        'quantity_base' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
