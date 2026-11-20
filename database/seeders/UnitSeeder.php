<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\Unit;
use App\Domain\Identity\Models\Shop;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run()
    {
        $shopId = Shop::query()->value('id');

        if (! $shopId) {
            return;
        }

        $units = [
            ['name' => 'Piece', 'abbreviation' => 'Pcs'],
            ['name' => 'Box', 'abbreviation' => 'Box'],
            ['name' => 'Carton', 'abbreviation' => 'Carton'],
        ];

        foreach ($units as $unit) {
            Unit::query()->firstOrCreate(
                [
                    'shop_id' => $shopId,
                    'abbreviation' => $unit['abbreviation'],
                ],
                ['name' => $unit['name']]
            );
        }
    }
}
