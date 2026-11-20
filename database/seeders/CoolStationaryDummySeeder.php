<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\BundleComponent;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\ProductVariantUnitPrice;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Catalog\Models\UnitConversion;
use App\Domain\Catalog\Models\VariantAttribute;
use App\Domain\Catalog\Models\VariantAttributeValue;
use App\Domain\Customers\Models\Customer;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\Shop;
use App\Domain\Identity\Models\Terminal;
use App\Domain\Inventory\Models\StockBalance;
use App\Domain\Inventory\Models\StockLot;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Purchasing\Models\Purchase;
use App\Domain\Purchasing\Models\PurchaseItem;
use App\Domain\Purchasing\Models\Supplier;
use App\Domain\Purchasing\Models\SupplierPriceHistory;
use App\Domain\Sales\Models\Payment;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Models\SaleItem;
use App\Domain\Shifts\Models\CashMovement;
use App\Domain\Shifts\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CoolStationaryDummySeeder extends Seeder
{
    public function run()
    {
        $shop = Shop::query()->where('name', 'Cool Stationary')->first()
            ?? Shop::query()->first();

        if (! $shop) {
            return;
        }

        $shopId = $shop->id;

        $roles = Role::query()->pluck('id', 'name');
        $units = Unit::query()->where('shop_id', $shopId)->get()->keyBy('abbreviation');

        $owner = User::query()->updateOrCreate(
            ['login_id' => 'A001'],
            [
                'shop_id' => $shopId,
                'role_id' => $roles['owner'],
                'name' => 'Cool Owner',
                'email' => 'admin@gmail.com',
                'phone' => '09751155115',
                'password' => Hash::make('admin123'),
                'pin_hash' => Hash::make('1234'),
                'is_active' => true,
            ]
        );

        $manager = User::query()->updateOrCreate(
            ['login_id' => 'M001'],
            [
                'shop_id' => $shopId,
                'role_id' => $roles['manager'],
                'name' => 'Aye Manager',
                'email' => 'manager@coolstationary.test',
                'phone' => '09123456789',
                'password' => Hash::make('manager123'),
                'pin_hash' => Hash::make('2345'),
                'is_active' => true,
            ]
        );

        $cashier = User::query()->updateOrCreate(
            ['login_id' => 'S001'],
            [
                'shop_id' => $shopId,
                'role_id' => $roles['cashier'],
                'name' => 'Su Cashier',
                'email' => 'cashier@coolstationary.test',
                'phone' => '09987654321',
                'password' => Hash::make('cashier123'),
                'is_active' => true,
            ]
        );

        $terminal = Terminal::query()->updateOrCreate(
            ['code' => 'CS-01'],
            [
                'shop_id' => $shopId,
                'name' => 'Front Counter 1',
                'receipt_printer' => 'epson-tm-t82',
                'label_printer' => 'xprinter-365b',
                'drawer_enabled' => true,
                'is_active' => true,
                'last_seen_at' => now(),
            ]
        );

        $categories = collect([
            'Writing',
            'Paper',
            'Files & Folders',
            'Art Supplies',
            'School Sets',
            'Correction & Glue',
        ])->mapWithKeys(function ($name, $index) use ($shopId) {
            $category = Category::query()->firstOrCreate(
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

            return [$name => $category];
        });

        $brands = collect(['Stabilo', 'Faber-Castell', 'Pilot', 'Double A', 'Deli', 'Pentel'])
            ->mapWithKeys(function ($name) use ($shopId) {
                $brand = Brand::query()->firstOrCreate(
                    ['shop_id' => $shopId, 'name' => $name],
                    ['is_active' => true]
                );

                return [$name => $brand];
            });

        $colorAttr = VariantAttribute::query()->firstOrCreate(
            ['shop_id' => $shopId, 'name' => 'Color']
        );
        $tipAttr = VariantAttribute::query()->firstOrCreate(
            ['shop_id' => $shopId, 'name' => 'Tip Size']
        );
        $rulingAttr = VariantAttribute::query()->firstOrCreate(
            ['shop_id' => $shopId, 'name' => 'Ruling Type']
        );
        $sizeAttr = VariantAttribute::query()->firstOrCreate(
            ['shop_id' => $shopId, 'name' => 'Paper Size']
        );

        $attrValues = [];
        foreach ([
            [$colorAttr->id, ['Blue', 'Black', 'Red', 'Green']],
            [$tipAttr->id, ['0.5mm', '0.7mm', '1.0mm']],
            [$rulingAttr->id, ['Single Line', 'Square', 'Blank']],
            [$sizeAttr->id, ['A4', 'A5', 'B5']],
        ] as [$attributeId, $values]) {
            foreach ($values as $value) {
                $attrValues[$attributeId.'|'.$value] = VariantAttributeValue::query()->firstOrCreate(
                    ['attribute_id' => $attributeId, 'value' => $value]
                );
            }
        }

        $productsSeed = [
            [
                'name' => 'Pilot G2 Gel Pen',
                'category' => 'Writing',
                'brand' => 'Pilot',
                'type' => 'standard',
                'track_expiry' => false,
                'variants' => [
                    ['name' => 'Blue / 0.5mm', 'sku' => 'PEN-G2-BLU-05', 'barcode' => '8850123000011', 'retail' => 1500, 'wholesale' => 1200, 'min_wholesale' => 12, 'cost' => 800, 'reorder' => 24, 'qty' => 180, 'attrs' => ['Color' => 'Blue', 'Tip Size' => '0.5mm']],
                    ['name' => 'Black / 0.7mm', 'sku' => 'PEN-G2-BLK-07', 'barcode' => '8850123000012', 'retail' => 1500, 'wholesale' => 1200, 'min_wholesale' => 12, 'cost' => 800, 'reorder' => 24, 'qty' => 140, 'attrs' => ['Color' => 'Black', 'Tip Size' => '0.7mm']],
                    ['name' => 'Red / 0.5mm', 'sku' => 'PEN-G2-RED-05', 'barcode' => '8850123000013', 'retail' => 1500, 'wholesale' => 1200, 'min_wholesale' => 12, 'cost' => 800, 'reorder' => 10, 'qty' => 8, 'attrs' => ['Color' => 'Red', 'Tip Size' => '0.5mm']],
                ],
                'conversions' => ['Box' => 12, 'Carton' => 120],
            ],
            [
                'name' => 'Stabilo Point 88 Fineliner',
                'category' => 'Writing',
                'brand' => 'Stabilo',
                'type' => 'standard',
                'track_expiry' => false,
                'variants' => [
                    ['name' => 'Green / 0.4mm', 'sku' => 'PEN-STB-GRN', 'barcode' => '4006381333630', 'retail' => 1800, 'wholesale' => 1500, 'min_wholesale' => 10, 'cost' => 950, 'reorder' => 20, 'qty' => 95, 'attrs' => ['Color' => 'Green']],
                    ['name' => 'Blue / 0.4mm', 'sku' => 'PEN-STB-BLU', 'barcode' => '4006381333647', 'retail' => 1800, 'wholesale' => 1500, 'min_wholesale' => 10, 'cost' => 950, 'reorder' => 20, 'qty' => 110, 'attrs' => ['Color' => 'Blue']],
                ],
                'conversions' => ['Box' => 10, 'Carton' => 100],
            ],
            [
                'name' => 'Double A Copier Paper',
                'category' => 'Paper',
                'brand' => 'Double A',
                'type' => 'standard',
                'track_expiry' => false,
                'variants' => [
                    ['name' => 'A4 / 80gsm', 'sku' => 'PPR-DA-A4', 'barcode' => '8859093800118', 'retail' => 14500, 'wholesale' => 13200, 'min_wholesale' => 5, 'cost' => 11000, 'reorder' => 10, 'qty' => 48, 'attrs' => ['Paper Size' => 'A4']],
                    ['name' => 'A5 / 80gsm', 'sku' => 'PPR-DA-A5', 'barcode' => '8859093800125', 'retail' => 9800, 'wholesale' => 8900, 'min_wholesale' => 5, 'cost' => 7500, 'reorder' => 8, 'qty' => 6, 'attrs' => ['Paper Size' => 'A5']],
                ],
                'conversions' => ['Carton' => 5],
            ],
            [
                'name' => 'Student Exercise Book',
                'category' => 'Paper',
                'brand' => 'Deli',
                'type' => 'standard',
                'track_expiry' => false,
                'variants' => [
                    ['name' => 'A5 / Single Line', 'sku' => 'NBK-A5-LINE', 'barcode' => '8851002001011', 'retail' => 1200, 'wholesale' => 950, 'min_wholesale' => 20, 'cost' => 700, 'reorder' => 40, 'qty' => 220, 'attrs' => ['Paper Size' => 'A5', 'Ruling Type' => 'Single Line']],
                    ['name' => 'B5 / Square', 'sku' => 'NBK-B5-SQR', 'barcode' => '8851002001028', 'retail' => 1500, 'wholesale' => 1200, 'min_wholesale' => 20, 'cost' => 850, 'reorder' => 30, 'qty' => 160, 'attrs' => ['Paper Size' => 'B5', 'Ruling Type' => 'Square']],
                ],
                'conversions' => ['Box' => 20, 'Carton' => 100],
            ],
            [
                'name' => 'Deli Document File',
                'category' => 'Files & Folders',
                'brand' => 'Deli',
                'type' => 'standard',
                'track_expiry' => false,
                'variants' => [
                    ['name' => 'Blue A4', 'sku' => 'FIL-DELI-BLU', 'barcode' => '692003', 'retail' => 2500, 'wholesale' => 2100, 'min_wholesale' => 10, 'cost' => 1500, 'reorder' => 15, 'qty' => 70, 'attrs' => ['Color' => 'Blue', 'Paper Size' => 'A4']],
                    ['name' => 'Black A4', 'sku' => 'FIL-DELI-BLK', 'barcode' => 'CS004', 'retail' => 2500, 'wholesale' => 2100, 'min_wholesale' => 10, 'cost' => 1500, 'reorder' => 15, 'qty' => 55, 'attrs' => ['Color' => 'Black', 'Paper Size' => 'A4']],
                ],
                'conversions' => ['Box' => 10],
            ],
            [
                'name' => 'Faber-Castell Color Pencils 12',
                'category' => 'Art Supplies',
                'brand' => 'Faber-Castell',
                'type' => 'standard',
                'track_expiry' => false,
                'variants' => [
                    ['name' => '12 Colors', 'sku' => 'ART-FC-12', 'barcode' => '4005401201126', 'retail' => 8500, 'wholesale' => 7600, 'min_wholesale' => 6, 'cost' => 5800, 'reorder' => 12, 'qty' => 40, 'attrs' => []],
                ],
                'conversions' => ['Box' => 6, 'Carton' => 36],
            ],
            [
                'name' => 'Pentel Correction Fluid',
                'category' => 'Correction & Glue',
                'brand' => 'Pentel',
                'type' => 'standard',
                'track_expiry' => true,
                'variants' => [
                    ['name' => 'White 18ml', 'sku' => 'COR-PNT-18', 'barcode' => '4902506165015', 'retail' => 2200, 'wholesale' => 1900, 'min_wholesale' => 12, 'cost' => 1300, 'reorder' => 20, 'qty' => 36, 'attrs' => [], 'lot' => ['code' => 'LOT-COR-2401', 'expiry' => now()->subDays(10)->toDateString(), 'qty' => 6]],
                ],
                'conversions' => ['Box' => 12],
            ],
            [
                'name' => 'Deli Liquid Glue',
                'category' => 'Correction & Glue',
                'brand' => 'Deli',
                'type' => 'standard',
                'track_expiry' => true,
                'variants' => [
                    ['name' => '50ml', 'sku' => 'GLU-DELI-50', 'barcode' => 'CS005', 'retail' => 1800, 'wholesale' => 1500, 'min_wholesale' => 12, 'cost' => 900, 'reorder' => 18, 'qty' => 64, 'attrs' => [], 'lot' => ['code' => 'LOT-GLU-2608', 'expiry' => now()->addMonths(8)->toDateString(), 'qty' => 64]],
                ],
                'conversions' => ['Box' => 12, 'Carton' => 72],
            ],
        ];

        $variantMap = [];

        DB::transaction(function () use (
            $productsSeed,
            $shopId,
            $categories,
            $brands,
            $units,
            $attrValues,
            $colorAttr,
            $tipAttr,
            $rulingAttr,
            $sizeAttr,
            $owner,
            &$variantMap
        ) {
            $attrLookup = [
                'Color' => $colorAttr,
                'Tip Size' => $tipAttr,
                'Ruling Type' => $rulingAttr,
                'Paper Size' => $sizeAttr,
            ];

            foreach ($productsSeed as $item) {
                $product = Product::query()->updateOrCreate(
                    [
                        'shop_id' => $shopId,
                        'name' => $item['name'],
                    ],
                    [
                        'category_id' => $categories[$item['category']]->id,
                        'brand_id' => $brands[$item['brand']]->id,
                        'base_unit_id' => $units['Pcs']->id,
                        'description' => $item['name'].' for Cool Stationary demo stock.',
                        'product_type' => $item['type'],
                        'track_stock' => true,
                        'track_expiry' => $item['track_expiry'],
                        'is_active' => true,
                    ]
                );

                UnitConversion::query()->firstOrCreate(
                    ['product_id' => $product->id, 'unit_id' => $units['Pcs']->id],
                    ['factor_to_base' => 1]
                );

                foreach ($item['conversions'] as $abbr => $factor) {
                    if (! isset($units[$abbr])) {
                        continue;
                    }

                    UnitConversion::query()->firstOrCreate(
                        ['product_id' => $product->id, 'unit_id' => $units[$abbr]->id],
                        ['factor_to_base' => $factor]
                    );
                }

                foreach ($item['variants'] as $variantData) {
                    $variant = ProductVariant::query()->updateOrCreate(
                        ['sku' => $variantData['sku']],
                        [
                            'product_id' => $product->id,
                            'barcode' => $variantData['barcode'],
                            'name' => $variantData['name'],
                            'retail_price' => $variantData['retail'],
                            'wholesale_price' => $variantData['wholesale'],
                            'min_wholesale_qty' => $variantData['min_wholesale'],
                            'cost_price' => $variantData['cost'],
                            'reorder_level' => $variantData['reorder'],
                            'is_active' => true,
                        ]
                    );

                    foreach ($variantData['attrs'] as $attrName => $value) {
                        $attribute = $attrLookup[$attrName];
                        $attributeValueId = $attrValues[$attribute->id.'|'.$value]->id;

                        $exists = DB::table('product_variant_values')
                            ->where('variant_id', $variant->id)
                            ->where('attribute_value_id', $attributeValueId)
                            ->exists();

                        if (! $exists) {
                            DB::table('product_variant_values')->insert([
                                'id' => (string) Str::ulid(),
                                'variant_id' => $variant->id,
                                'attribute_value_id' => $attributeValueId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    ProductVariantUnitPrice::query()->updateOrCreate(
                        ['variant_id' => $variant->id, 'unit_id' => $units['Pcs']->id],
                        [
                            'price' => $variantData['retail'],
                            'is_active' => true,
                        ]
                    );

                    if (isset($item['conversions']['Box'], $units['Box'])) {
                        ProductVariantUnitPrice::query()->updateOrCreate(
                            ['variant_id' => $variant->id, 'unit_id' => $units['Box']->id],
                            [
                                'price' => $variantData['wholesale'] * $item['conversions']['Box'],
                                'is_active' => true,
                            ]
                        );
                    }

                    if (isset($item['conversions']['Carton'], $units['Carton'])) {
                        ProductVariantUnitPrice::query()->updateOrCreate(
                            ['variant_id' => $variant->id, 'unit_id' => $units['Carton']->id],
                            [
                                'price' => $variantData['wholesale'] * $item['conversions']['Carton'],
                                'is_active' => true,
                            ]
                        );
                    }

                    $balance = StockBalance::query()->updateOrCreate(
                        [
                            'shop_id' => $shopId,
                            'variant_id' => $variant->id,
                        ],
                        ['qty_on_hand' => $variantData['qty']]
                    );

                    if (! empty($variantData['lot'])) {
                        StockLot::query()->updateOrCreate(
                            [
                                'variant_id' => $variant->id,
                                'lot_code' => $variantData['lot']['code'],
                            ],
                            [
                                'expiry_date' => $variantData['lot']['expiry'],
                                'qty_on_hand' => $variantData['lot']['qty'],
                            ]
                        );
                    }

                    if (! StockMovement::query()->where('variant_id', $variant->id)->where('movement_type', 'opening')->exists()) {
                        StockMovement::query()->create([
                            'shop_id' => $shopId,
                            'variant_id' => $variant->id,
                            'movement_type' => 'opening',
                            'qty_delta' => $variantData['qty'],
                            'qty_after' => $balance->qty_on_hand,
                            'unit_cost' => $variantData['cost'],
                            'reference_type' => 'opening',
                            'reference_id' => $variant->id,
                            'reason' => 'Demo opening stock',
                            'occurred_at' => now()->subDays(14),
                            'created_by' => $owner->id,
                        ]);
                    }

                    $variantMap[$variantData['sku']] = $variant;
                }
            }
        });

        $bundle = Product::query()->updateOrCreate(
            [
                'shop_id' => $shopId,
                'name' => 'School Starter Set',
            ],
            [
                'category_id' => $categories['School Sets']->id,
                'brand_id' => null,
                'base_unit_id' => $units['Pcs']->id,
                'description' => '1 Blue G2 pen + 1 exercise book + 1 glue.',
                'product_type' => 'bundle',
                'track_stock' => false,
                'track_expiry' => false,
                'is_active' => true,
            ]
        );

        UnitConversion::query()->firstOrCreate(
            ['product_id' => $bundle->id, 'unit_id' => $units['Pcs']->id],
            ['factor_to_base' => 1]
        );

        $bundleVariant = ProductVariant::query()->updateOrCreate(
            ['sku' => 'BND-SCHOOL-01'],
            [
                'product_id' => $bundle->id,
                'barcode' => 'CSBUNDLE01',
                'name' => 'School Starter Set',
                'retail_price' => 4200,
                'wholesale_price' => 3800,
                'min_wholesale_qty' => 10,
                'cost_price' => 2400,
                'reorder_level' => 0,
                'is_active' => true,
            ]
        );

        foreach ([
            ['sku' => 'PEN-G2-BLU-05', 'qty' => 1],
            ['sku' => 'NBK-A5-LINE', 'qty' => 1],
            ['sku' => 'GLU-DELI-50', 'qty' => 1],
        ] as $component) {
            BundleComponent::query()->updateOrCreate(
                [
                    'bundle_product_id' => $bundle->id,
                    'component_variant_id' => $variantMap[$component['sku']]->id,
                ],
                ['quantity_base' => $component['qty']]
            );
        }

        StockBalance::query()->updateOrCreate(
            ['shop_id' => $shopId, 'variant_id' => $bundleVariant->id],
            ['qty_on_hand' => 0]
        );

        ProductVariantUnitPrice::query()->updateOrCreate(
            ['variant_id' => $bundleVariant->id, 'unit_id' => $units['Pcs']->id],
            [
                'price' => 4200,
                'is_active' => true,
            ]
        );

        $variantMap['BND-SCHOOL-01'] = $bundleVariant;

        $customers = [
            ['name' => 'Walk-in Regular', 'phone' => '09111111111', 'type' => 'regular', 'limit' => 0],
            ['name' => 'Green Valley School', 'phone' => '09222222222', 'type' => 'b2b', 'limit' => 500000],
            ['name' => 'Bright Kids Tuition', 'phone' => '09333333333', 'type' => 'b2b', 'limit' => 250000],
            ['name' => 'U Myo Stationery Reseller', 'phone' => '09444444444', 'type' => 'b2b', 'limit' => 300000],
        ];

        $customerModels = [];
        foreach ($customers as $customer) {
            $customerModels[] = Customer::query()->updateOrCreate(
                [
                    'shop_id' => $shopId,
                    'phone' => $customer['phone'],
                ],
                [
                    'name' => $customer['name'],
                    'email' => Str::slug($customer['name']).'@example.test',
                    'customer_type' => $customer['type'],
                    'credit_limit' => $customer['limit'],
                    'balance' => 0,
                    'is_active' => true,
                ]
            );
        }

        $supplier = Supplier::query()->updateOrCreate(
            [
                'shop_id' => $shopId,
                'name' => 'Yangon Stationery Wholesale',
            ],
            [
                'phone' => '09770001122',
                'email' => 'sales@ysw.example',
                'address' => 'Lanmadaw, Yangon',
                'opening_balance' => 0,
                'is_active' => true,
            ]
        );

        Supplier::query()->updateOrCreate(
            [
                'shop_id' => $shopId,
                'name' => 'Mandalay Office Mart',
            ],
            [
                'phone' => '09770003344',
                'email' => 'order@mom.example',
                'address' => 'Chanayethazan, Mandalay',
                'opening_balance' => 0,
                'is_active' => true,
            ]
        );

        $purchase = Purchase::query()->updateOrCreate(
            ['purchase_no' => 'PO-DEMO-0001'],
            [
                'shop_id' => $shopId,
                'supplier_id' => $supplier->id,
                'terminal_id' => $terminal->id,
                'status' => 'received',
                'payment_type' => 'credit',
                'purchased_at' => now()->subDays(7),
                'due_date' => now()->addDays(23)->toDateString(),
                'subtotal' => 132000,
                'discount_amount' => 2000,
                'total' => 130000,
                'amount_paid' => 50000,
                'notes' => 'Demo credit purchase',
                'created_by' => $manager->id,
            ]
        );

        $purchaseLines = [
            ['sku' => 'PEN-G2-BLU-05', 'unit' => 'Box', 'qty' => 5, 'cost' => 9600],
            ['sku' => 'PPR-DA-A4', 'unit' => 'Pcs', 'qty' => 10, 'cost' => 11000],
        ];

        foreach ($purchaseLines as $line) {
            $variant = $variantMap[$line['sku']];
            $unit = $units[$line['unit']];
            $factor = UnitConversion::query()
                ->where('product_id', $variant->product_id)
                ->where('unit_id', $unit->id)
                ->value('factor_to_base') ?: 1;
            $qtyBase = $line['qty'] * $factor;
            $lineTotal = $line['qty'] * $line['cost'];

            $purchaseItem = PurchaseItem::query()->updateOrCreate(
                [
                    'purchase_id' => $purchase->id,
                    'variant_id' => $variant->id,
                    'unit_id' => $unit->id,
                ],
                [
                    'quantity' => $line['qty'],
                    'factor_to_base' => $factor,
                    'quantity_base' => $qtyBase,
                    'unit_cost' => $line['cost'],
                    'line_total' => $lineTotal,
                ]
            );

            SupplierPriceHistory::query()->firstOrCreate(
                [
                    'supplier_id' => $supplier->id,
                    'variant_id' => $variant->id,
                    'purchase_item_id' => $purchaseItem->id,
                ],
                [
                    'unit_id' => $unit->id,
                    'unit_cost' => $line['cost'],
                    'effective_at' => $purchase->purchased_at,
                ]
            );
        }

        $shift = Shift::query()->where('terminal_id', $terminal->id)->where('status', 'open')->first();

        if (! $shift) {
            $shift = Shift::query()->create([
                'shop_id' => $shopId,
                'terminal_id' => $terminal->id,
                'user_id' => $cashier->id,
                'status' => 'open',
                'opened_at' => now()->subHours(3),
                'opening_cash' => 50000,
                'notes' => 'Demo open shift',
            ]);

            CashMovement::query()->create([
                'shift_id' => $shift->id,
                'type' => 'cash_in',
                'amount' => 10000,
                'reason' => 'Float top-up',
                'created_by' => $manager->id,
                'occurred_at' => now()->subHours(2),
            ]);
        }

        $saleSpecs = [
            [
                'sale_no' => 'CS-01-DEMO0001',
                'customer' => null,
                'status' => 'completed',
                'tier' => 'retail',
                'payment' => 'cash',
                'items' => [
                    ['sku' => 'PEN-G2-BLU-05', 'qty' => 2],
                    ['sku' => 'NBK-A5-LINE', 'qty' => 1],
                ],
            ],
            [
                'sale_no' => 'CS-01-DEMO0002',
                'customer' => 1,
                'status' => 'completed',
                'tier' => 'wholesale',
                'payment' => 'credit',
                'items' => [
                    ['sku' => 'PEN-G2-BLK-07', 'qty' => 12],
                    ['sku' => 'PPR-DA-A4', 'qty' => 2],
                ],
            ],
            [
                'sale_no' => 'CS-01-DEMO0003',
                'customer' => null,
                'status' => 'parked',
                'tier' => 'retail',
                'payment' => null,
                'items' => [
                    ['sku' => 'BND-SCHOOL-01', 'qty' => 1],
                    ['sku' => 'ART-FC-12', 'qty' => 1],
                ],
            ],
        ];

        foreach ($saleSpecs as $spec) {
            if (Sale::query()->where('sale_no', $spec['sale_no'])->exists()) {
                continue;
            }

            $lines = [];
            $subtotal = 0;

            foreach ($spec['items'] as $item) {
                $variant = $variantMap[$item['sku']];
                $unitPrice = $spec['tier'] === 'wholesale' ? $variant->wholesale_price : $variant->retail_price;
                if ($variant->min_wholesale_qty && $item['qty'] >= $variant->min_wholesale_qty) {
                    $unitPrice = $variant->wholesale_price;
                    $tier = 'wholesale';
                } else {
                    $tier = 'retail';
                }

                $lineTotal = $unitPrice * $item['qty'];
                $subtotal += $lineTotal;
                $lines[] = [
                    'variant' => $variant,
                    'qty' => $item['qty'],
                    'unit_price' => $unitPrice,
                    'tier' => $tier,
                    'line_total' => $lineTotal,
                    'is_bundle' => $variant->product->product_type === 'bundle',
                ];
            }

            $sale = Sale::query()->create([
                'shop_id' => $shopId,
                'terminal_id' => $terminal->id,
                'shift_id' => $shift->id,
                'customer_id' => $spec['customer'] !== null ? $customerModels[$spec['customer']]->id : null,
                'cashier_id' => $cashier->id,
                'sale_no' => $spec['sale_no'],
                'status' => $spec['status'],
                'price_tier' => $spec['tier'],
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'discount_percent' => 0,
                'tax_amount' => 0,
                'total' => $subtotal,
                'amount_paid' => $spec['status'] === 'completed' && $spec['payment'] === 'cash' ? $subtotal : 0,
                'amount_due' => $spec['status'] === 'completed' && $spec['payment'] === 'credit' ? $subtotal : 0,
                'payment_status' => $spec['status'] === 'parked'
                    ? 'unpaid'
                    : ($spec['payment'] === 'credit' ? 'credit' : 'paid'),
                'sold_at' => now()->subMinutes(rand(20, 120)),
            ]);

            foreach ($lines as $line) {
                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'variant_id' => $line['variant']->id,
                    'product_id' => $line['variant']->product_id,
                    'description' => $line['variant']->product->name.' · '.$line['variant']->name,
                    'quantity_base' => $line['qty'],
                    'unit_price' => $line['unit_price'],
                    'price_tier' => $line['tier'],
                    'discount_amount' => 0,
                    'line_total' => $line['line_total'],
                    'is_bundle' => $line['is_bundle'],
                ]);

                if ($sale->status !== 'completed') {
                    continue;
                }

                if ($line['is_bundle']) {
                    foreach ($line['variant']->product->bundleComponents as $component) {
                        $componentVariant = $component->componentVariant;
                        $qtyOut = $component->quantity_base * $line['qty'];
                        $balance = StockBalance::query()->firstOrCreate(
                            ['shop_id' => $shopId, 'variant_id' => $componentVariant->id],
                            ['qty_on_hand' => 0]
                        );
                        $balance->qty_on_hand = max(0, $balance->qty_on_hand - $qtyOut);
                        $balance->save();

                        StockMovement::query()->create([
                            'shop_id' => $shopId,
                            'variant_id' => $componentVariant->id,
                            'movement_type' => 'bundle_component_out',
                            'qty_delta' => -$qtyOut,
                            'qty_after' => $balance->qty_on_hand,
                            'unit_cost' => $componentVariant->cost_price,
                            'reference_type' => Sale::class,
                            'reference_id' => $sale->id,
                            'reason' => 'Bundle sale '.$sale->sale_no,
                            'occurred_at' => $sale->sold_at,
                            'created_by' => $cashier->id,
                        ]);
                    }
                } else {
                    $balance = StockBalance::query()->firstOrCreate(
                        ['shop_id' => $shopId, 'variant_id' => $line['variant']->id],
                        ['qty_on_hand' => 0]
                    );
                    $balance->qty_on_hand = max(0, $balance->qty_on_hand - $line['qty']);
                    $balance->save();

                    StockMovement::query()->create([
                        'shop_id' => $shopId,
                        'variant_id' => $line['variant']->id,
                        'movement_type' => 'sale_out',
                        'qty_delta' => -$line['qty'],
                        'qty_after' => $balance->qty_on_hand,
                        'unit_cost' => $line['variant']->cost_price,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'reason' => 'Sale '.$sale->sale_no,
                        'occurred_at' => $sale->sold_at,
                        'created_by' => $cashier->id,
                    ]);
                }
            }

            if ($sale->status === 'completed' && $spec['payment'] === 'cash') {
                Payment::query()->create([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'shift_id' => $shift->id,
                    'method' => 'cash',
                    'amount' => $sale->total,
                    'tendered' => $sale->total + 1000,
                    'change_amount' => 1000,
                    'paid_at' => $sale->sold_at,
                    'created_by' => $cashier->id,
                ]);

                CashMovement::query()->create([
                    'shift_id' => $shift->id,
                    'type' => 'sale',
                    'amount' => $sale->total,
                    'reason' => 'Cash sale '.$sale->sale_no,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'created_by' => $cashier->id,
                    'occurred_at' => $sale->sold_at,
                ]);
            }

            if ($sale->status === 'completed' && $spec['payment'] === 'credit') {
                Payment::query()->create([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'shift_id' => $shift->id,
                    'method' => 'credit',
                    'amount' => $sale->total,
                    'paid_at' => $sale->sold_at,
                    'created_by' => $cashier->id,
                ]);

                $customer = $customerModels[$spec['customer']];
                $customer->balance = $customer->balance + $sale->total;
                $customer->save();
            }
        }

        $this->command?->info('Cool Stationary dummy data ready.');
        $this->command?->info('Owner: A001 / admin123 (PIN 1234)');
        $this->command?->info('Manager: M001 / manager123 (PIN 2345)');
        $this->command?->info('Cashier: S001 / cashier123');
    }
}
