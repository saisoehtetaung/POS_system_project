<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ReadsAssignedRole
{
    protected function roleAllows(User $user, string $ability): bool
    {
        $user->loadMissing('role');

        return (bool) optional($user->role)->{$ability};
    }
}
