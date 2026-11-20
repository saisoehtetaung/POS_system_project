<?php

namespace App\Policies;

use App\Domain\Purchasing\Models\Purchase;
use App\Models\User;
use App\Policies\Concerns\ReadsAssignedRole;

class PurchasePolicy
{
    use ReadsAssignedRole;

    public function viewAny(User $user): bool
    {
        return $this->roleAllows($user, 'can_manage_purchases');
    }

    public function view(User $user, Purchase $purchase): bool
    {
        return $this->roleAllows($user, 'can_manage_purchases');
    }

    public function create(User $user): bool
    {
        return $this->roleAllows($user, 'can_manage_purchases');
    }

    public function update(User $user, Purchase $purchase): bool
    {
        return $this->roleAllows($user, 'can_manage_purchases');
    }
}
