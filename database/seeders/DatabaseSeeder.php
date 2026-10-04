<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
            TechnicianSeeder::class,
            MachineSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            MachineSlotSeeder::class,
            ProductInventorySeeder::class,
        ]);
    }
}