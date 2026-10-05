<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignUserRolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'name');

        User::where('email', 'admin@vending.test')->update([
            'role_id' => $roles['admin'],
        ]);

        User::where('email', 'technician@vending.test')->update([
            'role_id' => $roles['technician'],
        ]);

        User::where('email', 'customer@vending.test')->update([
            'role_id' => $roles['customer'],
        ]);
    }
}