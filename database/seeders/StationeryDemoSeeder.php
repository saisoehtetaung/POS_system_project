<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use App\Domain\Identity\Models\Shop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StationeryDemoSeeder extends Seeder
{
    public function run()
    {
        $shopId = Shop::query()->value('id');

        if (! $shopId) {
            return;
        }

        $categories = ['Writing', 'Paper', 'Files & Folders', 'Art Supplies', 'School Sets'];
        foreach ($categories as $index => $name) {
            Category::query()->firstOrCreate(
                [
                    'shop_id' => $shopId,
                    'slug' => Str::slug($name),
                ],
                [
                    'name' => $name,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }

        foreach (['Stabilo', 'Faber-Castell', 'Pilot', 'Double A'] as $brand) {
            Brand::query()->firstOrCreate(
                [
                    'shop_id' => $shopId,
                    'name' => $brand,
                ],
                ['is_active' => true]
            );
        }
    }
}
