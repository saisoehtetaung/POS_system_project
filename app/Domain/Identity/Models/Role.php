<?php

namespace App\Domain\Identity\Models;

use App\Domain\Concerns\PosModel;
use App\Models\User;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends PosModel
{
    use HasFactory;

    protected static function newFactory()
    {
        return RoleFactory::new();
    }

    protected $casts = [
        'max_discount_percentage' => 'decimal:2',
        'can_view_cost' => 'boolean',
        'can_void' => 'boolean',
        'can_refund' => 'boolean',
        'can_adjust_stock' => 'boolean',
        'can_manage_purchases' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
