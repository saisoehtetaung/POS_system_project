<?php

namespace Database\Factories;

use App\Domain\Identity\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->lexify('role_????'),
            'max_discount_percentage' => 5,
            'can_view_cost' => false,
            'can_void' => false,
            'can_refund' => false,
            'can_adjust_stock' => false,
            'can_manage_purchases' => false,
        ];
    }
}
