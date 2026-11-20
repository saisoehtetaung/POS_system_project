<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Concerns\PosModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundleComponent extends PosModel
{
    protected $casts = [
        'quantity_base' => 'decimal:3',
    ];

    public function bundleProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'bundle_product_id');
    }

    public function componentVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'component_variant_id');
    }
}
