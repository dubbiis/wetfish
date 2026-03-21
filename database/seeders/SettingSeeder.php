<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'tax_rate' => '21',
            'auto_margin_percentage' => '30',
            'business_name' => 'WetFish',
            'business_address' => 'c/ Tomás de Aquino 3, 14004 Córdoba',
            'business_nif' => '30984781D',
            'business_phone' => '',
            'business_email' => '',
            'business_logo' => '',
            'target_margin_percentage' => '30',
            'expense_calculation_period' => 'month',
            'price_adjustment_active' => '0',
            'price_adjustment_percentage' => '0',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
