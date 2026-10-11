<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\Product;
use App\Models\MachineSlot;
use Illuminate\Database\Seeder;

class MachineSlotSeeder extends Seeder
{
    public function run(): void
    {
        $machines = Machine::all();
        $products = Product::all();

        // Kode slot untuk mesin
        $slotCodes = ['A1', 'A2', 'A3', 'A4', 'B1', 'B2'];

        foreach ($machines as $machine) {
            foreach ($products as $index => $product) {
                $slotCode = $slotCodes[$index] ?? 'S' . ($index + 1);

                MachineSlot::create([
                    'machine_id' => $machine->id,
                    'product_id' => $product->id,
                    'slot_code' => $slotCode,
                    'capacity' => 10,
                    'current_qty' => 10,
                    'hold_qty' => 0,
                    'status' => 'active',
                ]);
            }
        }
    }
}