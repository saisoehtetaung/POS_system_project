<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Concerns\PosModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantUnitPrice extends PosModel
{
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
