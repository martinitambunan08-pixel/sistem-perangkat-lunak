<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Vending',
            'email' => 'admin@vending.test',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Teknisi Vending',
            'email' => 'technician@vending.test',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Customer Vending',
            'email' => 'customer@vending.test',
            'password' => Hash::make('password'),
        ]);
    }
}