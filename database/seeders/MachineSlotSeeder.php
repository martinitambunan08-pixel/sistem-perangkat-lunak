<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\MachineSlot;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MachineSlotSeeder extends Seeder
{
    public function run(): void
    {
        $machines = Machine::all();
        $products = Product::all();

        foreach ($machines as $machine) {
            foreach ($products as $index => $product) {
                MachineSlot::create([
                    'machine_id' => $machine->id,
                    'product_id' => $product->id,
                    'slot_number' => $index + 1,
                    'capacity' => 10,
                    'current_stock' => 10,
                    'status' => 'active',
                ]);
            }
        }
    }
}