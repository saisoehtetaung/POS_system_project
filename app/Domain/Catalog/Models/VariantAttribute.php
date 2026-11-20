<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Concerns\PosModel;
use App\Domain\Identity\Models\Shop;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VariantAttribute extends PosModel
{
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(VariantAttributeValue::class, 'attribute_id');
    }
}
