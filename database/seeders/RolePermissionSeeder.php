<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('name', 'admin')->first();
        $technician = Role::where('name', 'technician')->first();
        $customer = Role::where('name', 'customer')->first();

        $manageUsers = Permission::where('name', 'manage_users')->first();
        $manageMachines = Permission::where('name', 'manage_machines')->first();
        $manageProducts = Permission::where('name', 'manage_products')->first();
        $manageInventory = Permission::where('name', 'manage_inventory')->first();
        $manageOrders = Permission::where('name', 'manage_orders')->first();
        $managePayments = Permission::where('name', 'manage_payments')->first();
        $manageMaintenance = Permission::where('name', 'manage_maintenance')->first();
        $viewReports = Permission::where('name', 'view_reports')->first();
        $submitSupportTicket = Permission::where('name', 'submit_support_ticket')->first();

        $admin->permissions()->attach([
            $manageUsers->id,
            $manageMachines->id,
            $manageProducts->id,
            $manageInventory->id,
            $manageOrders->id,
            $managePayments->id,
            $manageMaintenance->id,
            $viewReports->id,
        ]);

        $technician->permissions()->attach([
            $manageMachines->id,
            $manageInventory->id,
            $manageMaintenance->id,
        ]);

        $customer->permissions()->attach([
            $submitSupportTicket->id,
            $manageOrders->id,
            $managePayments->id,
        ]);
    }
}