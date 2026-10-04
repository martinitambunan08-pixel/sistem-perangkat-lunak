<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'name' => 'admin',
            'description' => 'Administrator sistem',
        ]);

        Role::create([
            'name' => 'technician',
            'description' => 'Teknisi mesin vending',
        ]);

        Role::create([
            'name' => 'customer',
            'description' => 'Pelanggan vending machine',
        ]);
    }
}