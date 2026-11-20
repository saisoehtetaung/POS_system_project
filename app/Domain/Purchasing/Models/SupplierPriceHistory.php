<?php

namespace App\Domain\Purchasing\Models;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Concerns\PosModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPriceHistory extends PosModel
{
    public const UPDATED_AT = null;

    protected $table = 'supplier_price_history';

    protected $hidden = [
        'unit_cost',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'effective_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }
}
