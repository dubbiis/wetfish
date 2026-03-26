<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Peces', 'slug' => 'peces', 'icon' => 'set_meal'],
            ['name' => 'Plantas', 'slug' => 'plantas', 'icon' => 'eco'],
            ['name' => 'Accesorios', 'slug' => 'accesorios', 'icon' => 'category'],
            ['name' => 'Peces criadero', 'slug' => 'peces-criadero', 'icon' => 'water_drop'],
            ['name' => 'Plantas criadero', 'slug' => 'plantas-criadero', 'icon' => 'forest'],
            ['name' => 'Comida', 'slug' => 'comida', 'icon' => 'restaurant'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
