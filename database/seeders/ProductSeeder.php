<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $peces = Category::where('slug', 'peces')->first();

        $products = [
            ['code' => 'PEZ001', 'name' => 'Pez 1',  'cost_price' => 2.50,  'sale_price' => 3.25,  'stock' => 25, 'min_stock' => 5],
            ['code' => 'PEZ002', 'name' => 'Pez 2',  'cost_price' => 3.00,  'sale_price' => 3.90,  'stock' => 18, 'min_stock' => 5],
            ['code' => 'PEZ003', 'name' => 'Pez 3',  'cost_price' => 1.80,  'sale_price' => 2.34,  'stock' => 30, 'min_stock' => 5],
            ['code' => 'PEZ004', 'name' => 'Pez 4',  'cost_price' => 5.00,  'sale_price' => 6.50,  'stock' => 12, 'min_stock' => 3],
            ['code' => 'PEZ005', 'name' => 'Pez 5',  'cost_price' => 4.20,  'sale_price' => 5.46,  'stock' => 8,  'min_stock' => 5],
            ['code' => 'PEZ006', 'name' => 'Pez 6',  'cost_price' => 7.50,  'sale_price' => 9.75,  'stock' => 6,  'min_stock' => 3],
            ['code' => 'PEZ007', 'name' => 'Pez 7',  'cost_price' => 2.00,  'sale_price' => 2.60,  'stock' => 40, 'min_stock' => 10],
            ['code' => 'PEZ008', 'name' => 'Pez 8',  'cost_price' => 15.00, 'sale_price' => 19.50, 'stock' => 4,  'min_stock' => 2],
            ['code' => 'PEZ009', 'name' => 'Pez 9',  'cost_price' => 3.50,  'sale_price' => 4.55,  'stock' => 20, 'min_stock' => 5],
            ['code' => 'PEZ010', 'name' => 'Pez 10', 'cost_price' => 6.00,  'sale_price' => 7.80,  'stock' => 2,  'min_stock' => 5],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['code' => $product['code']],
                array_merge($product, [
                    'category_id' => $peces?->id,
                    'auto_margin' => true,
                ])
            );
        }
    }
}
