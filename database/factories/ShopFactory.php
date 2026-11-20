<?php

namespace Database\Factories;

use App\Domain\Identity\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'currency_code' => 'MMK',
            'tax_rate' => 0,
        ];
    }
}
