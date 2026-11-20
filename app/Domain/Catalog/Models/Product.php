<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Concerns\PosModel;
use App\Domain\Identity\Models\Shop;
use App\Domain\Identity\Models\Terminal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends PosModel
{
    use SoftDeletes;

    protected $casts = [
        'track_stock' => 'boolean',
        'track_expiry' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function originTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origin_terminal_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class);
    }

    public function bundleComponents(): HasMany
    {
        return $this->hasMany(BundleComponent::class, 'bundle_product_id');
    }

    public function imageUrl(): string
    {
        if ($this->image_path) {
            return asset('storage/'.$this->image_path);
        }

        return asset('images/cool/product-default.svg');
    }
}
