<?php

namespace App\Http\Resources\Concerns;

use App\Models\User;
use Illuminate\Http\Request;

trait HidesCostUnlessAuthorized
{
    protected function viewerCanSeeCost(Request $request): bool
    {
        $user = $request->user();

        return $user instanceof User && $user->canViewCost();
    }
}
