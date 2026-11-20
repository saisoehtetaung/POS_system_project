<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Concerns\PosModel;
use App\Domain\Identity\Models\Shop;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelTemplate extends PosModel
{
    protected $casts = [
        'width_mm' => 'decimal:2',
        'height_mm' => 'decimal:2',
        'show_shop_name' => 'boolean',
        'show_item_name' => 'boolean',
        'show_price' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
