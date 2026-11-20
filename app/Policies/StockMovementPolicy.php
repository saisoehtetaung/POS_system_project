<?php

namespace App\Policies;

use App\Domain\Inventory\Models\StockMovement;
use App\Models\User;
use App\Policies\Concerns\ReadsAssignedRole;

class StockMovementPolicy
{
    use ReadsAssignedRole;

    public function viewAny(User $user): bool
    {
        return $this->roleAllows($user, 'can_adjust_stock') || $user->canViewCost();
    }

    public function create(User $user): bool
    {
        return $this->roleAllows($user, 'can_adjust_stock');
    }

    public function view(User $user, StockMovement $movement): bool
    {
        return $this->roleAllows($user, 'can_adjust_stock') || $user->canViewCost();
    }
}
