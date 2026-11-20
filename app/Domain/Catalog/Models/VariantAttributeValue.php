<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Concerns\PosModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantAttributeValue extends PosModel
{
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(VariantAttribute::class, 'attribute_id');
    }
}
