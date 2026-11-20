<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Concerns\PosModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitConversion extends PosModel
{
    protected $casts = [
        'factor_to_base' => 'decimal:6',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
