<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Concerns\PosModel;
use App\Domain\Identity\Models\Terminal;
use App\Domain\Inventory\Models\StockBalance;
use App\Domain\Inventory\Models\StockLot;
use App\Domain\Inventory\Models\StockMovement;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends PosModel
{
    use SoftDeletes;

    protected $hidden = [
        'cost_price',
    ];

    protected $casts = [
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'min_wholesale_qty' => 'decimal:3',
        'cost_price' => 'decimal:2',
        'reorder_level' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function originTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origin_terminal_id');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            VariantAttributeValue::class,
            'product_variant_values',
            'variant_id',
            'attribute_value_id'
        )->withTimestamps();
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class, 'variant_id');
    }

    public function stockLots(): HasMany
    {
        return $this->hasMany(StockLot::class, 'variant_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    public function unitPrices(): HasMany
    {
        return $this->hasMany(ProductVariantUnitPrice::class, 'variant_id');
    }

    public function activeUnitPrices(): HasMany
    {
        return $this->unitPrices()->where('is_active', true);
    }
}
