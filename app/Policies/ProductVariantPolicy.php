<?php

namespace App\Policies;

use App\Domain\Catalog\Models\ProductVariant;
use App\Models\User;

class ProductVariantPolicy
{
    public function viewCost(User $user, ProductVariant $variant): bool
    {
        return $user->canViewCost();
    }
}
