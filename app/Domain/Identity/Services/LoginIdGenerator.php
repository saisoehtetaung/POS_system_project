<?php

namespace App\Domain\Identity\Services;

use App\Models\User;

class LoginIdGenerator
{
    public static function prefixForRole(string $roleName): string
    {
        return match ($roleName) {
            'owner' => 'A',
            'manager' => 'M',
            'cashier' => 'S',
            default => 'S',
        };
    }

    public static function next(string $shopId, string $roleName): string
    {
        $prefix = self::prefixForRole($roleName);

        $latest = User::withTrashed()
            ->where('shop_id', $shopId)
            ->where('login_id', 'like', $prefix.'%')
            ->orderByDesc('login_id')
            ->value('login_id');

        $sequence = 1;
        if (is_string($latest) && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
