<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Concerns\PosModel;
use App\Domain\Identity\Models\Shop;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends PosModel
{
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'base_unit_id');
    }
}
