<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Luz',          'icon' => 'bolt'],
            ['name' => 'Agua',         'icon' => 'water_drop'],
            ['name' => 'Teléfono',     'icon' => 'phone'],
            ['name' => 'Internet',     'icon' => 'wifi'],
            ['name' => 'Hosting',      'icon' => 'dns'],
            ['name' => 'Alquiler',     'icon' => 'home'],
            ['name' => 'Seguros',      'icon' => 'shield'],
            ['name' => 'Gestoría',     'icon' => 'calculate'],
            ['name' => 'Mantenimiento','icon' => 'build'],
            ['name' => 'Limpieza',     'icon' => 'cleaning_services'],
            ['name' => 'Cuota autónomo','icon' => 'badge'],
            ['name' => 'Otros',        'icon' => 'more_horiz'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
