<?php

namespace App\Policies;

use App\Domain\Sales\Models\Sale;
use App\Models\User;
use App\Policies\Concerns\ReadsAssignedRole;

class SalePolicy
{
    use ReadsAssignedRole;

    public function void(User $user, Sale $sale): bool
    {
        return $this->roleAllows($user, 'can_void');
    }

    public function refund(User $user, Sale $sale): bool
    {
        return $this->roleAllows($user, 'can_refund');
    }
}
