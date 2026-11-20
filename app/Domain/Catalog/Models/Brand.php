<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Concerns\PosModel;
use App\Domain\Identity\Models\Shop;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends PosModel
{
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
