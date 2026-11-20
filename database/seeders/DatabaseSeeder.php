<?php

namespace Database\Seeders;

use App\Domain\Identity\Models\Shop;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);

        Shop::query()->firstOrCreate(
            ['name' => 'Cool Stationary'],
            [
                'currency_code' => 'MMK',
                'tax_rate' => 0,
                'phone' => '09751155115',
                'address' => 'Yangon',
                'receipt_header' => 'Cool Stationary',
                'receipt_footer' => 'Thank you for shopping with us!',
            ]
        );

        $this->call([
            UnitSeeder::class,
            LabelTemplateSeeder::class,
            StationeryDemoSeeder::class,
            CoolStationaryDummySeeder::class,
        ]);
    }
}
