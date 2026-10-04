<?php

namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    public function run(): void
    {
        Machine::create([
            'name' => 'Vending Machine Kampus A',
            'location' => 'Gedung A - Lantai 1',
            'ip_address' => '192.168.1.101',
            'status' => 'active',
        ]);

        Machine::create([
            'name' => 'Vending Machine Kampus B',
            'location' => 'Gedung B - Lantai 1',
            'ip_address' => '192.168.1.102',
            'status' => 'active',
        ]);
    }
}