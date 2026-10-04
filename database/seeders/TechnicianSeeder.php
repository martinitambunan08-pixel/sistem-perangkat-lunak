<?php

namespace Database\Seeders;

use App\Models\Technician;
use App\Models\User;
use Illuminate\Database\Seeder;

class TechnicianSeeder extends Seeder
{
    public function run(): void
    {
        $technicianUser = User::where(
            'email',
            'technician@vending.test'
        )->first();

        Technician::create([
            'user_id' => $technicianUser->id,
            'employee_number' => 'TECH-001',
            'specialization' => 'Maintenance Mesin Vending',
            'phone' => '081298765432',
            'is_active' => true,
        ]);
    }
}