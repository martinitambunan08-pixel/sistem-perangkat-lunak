<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\Product;
use App\Models\ProductInventory;
use Illuminate\Database\Seeder;

class ProductInventorySeeder extends Seeder
{
    public function run(): void
    {
        $machines = Machine::all();
        $products = Product::all();

        foreach ($machines as $machine) {
            foreach ($products as $product) {
                ProductInventory::create([
                    'machine_id' => $machine->id,
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'minimum_stock' => 2,
                    'maximum_stock' => 10,
                    'last_restocked_at' => now(),
                ]);
            }
        }
    }
}