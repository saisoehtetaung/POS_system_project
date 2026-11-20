<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Concerns\PosModel;
use App\Domain\Identity\Models\Shop;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalance extends PosModel
{
    protected $casts = [
        'qty_on_hand' => 'decimal:3',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
