<?php

namespace Database\Seeders;

use App\Domain\Identity\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            [
                'name' => 'owner',
                'max_discount_percentage' => 100,
                'can_view_cost' => true,
                'can_void' => true,
                'can_refund' => true,
                'can_adjust_stock' => true,
                'can_manage_purchases' => true,
            ],
            [
                'name' => 'manager',
                'max_discount_percentage' => 20,
                'can_view_cost' => true,
                'can_void' => true,
                'can_refund' => true,
                'can_adjust_stock' => true,
                'can_manage_purchases' => true,
            ],
            [
                'name' => 'cashier',
                'max_discount_percentage' => 5,
                'can_view_cost' => false,
                'can_void' => false,
                'can_refund' => false,
                'can_adjust_stock' => false,
                'can_manage_purchases' => false,
            ],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
