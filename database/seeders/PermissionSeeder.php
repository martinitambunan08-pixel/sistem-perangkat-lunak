<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'manage_users', 'description' => 'Mengelola pengguna'],
            ['name' => 'manage_machines', 'description' => 'Mengelola mesin vending'],
            ['name' => 'manage_products', 'description' => 'Mengelola produk'],
            ['name' => 'manage_inventory', 'description' => 'Mengelola stok produk'],
            ['name' => 'manage_orders', 'description' => 'Mengelola pesanan'],
            ['name' => 'manage_payments', 'description' => 'Mengelola pembayaran'],
            ['name' => 'manage_maintenance', 'description' => 'Mengelola maintenance mesin'],
            ['name' => 'view_reports', 'description' => 'Melihat laporan'],
            ['name' => 'submit_support_ticket', 'description' => 'Membuat laporan bantuan'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}