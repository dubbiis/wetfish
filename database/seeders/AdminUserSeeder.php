<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@wetfish.es'],
            [
                'name' => 'Admin WetFish',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
    }
}
