<?php

namespace App\Domain\Identity\Models;

use App\Domain\Concerns\PosModel;
use App\Models\User;
use Database\Factories\ShopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends PosModel
{
    use HasFactory;

    protected static function newFactory()
    {
        return ShopFactory::new();
    }

    protected $casts = [
        'tax_rate' => 'decimal:4',
    ];

    public function terminals(): HasMany
    {
        return $this->hasMany(Terminal::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
