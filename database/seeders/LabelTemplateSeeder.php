<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\LabelTemplate;
use App\Domain\Identity\Models\Shop;
use Illuminate\Database\Seeder;

class LabelTemplateSeeder extends Seeder
{
    public function run()
    {
        $shopId = Shop::query()->value('id');

        if (! $shopId) {
            return;
        }

        $templates = [
            ['name' => 'Small 35x22mm', 'width_mm' => 35, 'height_mm' => 22],
            ['name' => 'Medium 50x30mm', 'width_mm' => 50, 'height_mm' => 30],
        ];

        foreach ($templates as $template) {
            LabelTemplate::query()->firstOrCreate(
                [
                    'shop_id' => $shopId,
                    'name' => $template['name'],
                ],
                [
                    'width_mm' => $template['width_mm'],
                    'height_mm' => $template['height_mm'],
                    'show_shop_name' => true,
                    'show_item_name' => true,
                    'show_price' => true,
                    'barcode_format' => 'code128',
                ]
            );
        }
    }
}
