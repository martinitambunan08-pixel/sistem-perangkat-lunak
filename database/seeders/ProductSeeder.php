<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $makananUtama = ProductCategory::where(
            'name',
            'Makanan Utama'
        )->first();

        $snack = ProductCategory::where(
            'name',
            'Snack'
        )->first();

        $minuman = ProductCategory::where(
            'name',
            'Minuman'
        )->first();

        Product::create([
            'category_id' => $makananUtama->id,
            'name' => 'Nasi Goreng',
            'description' => 'Nasi goreng hangat siap disajikan',
            'price' => 15000,
            'image' => null,
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $makananUtama->id,
            'name' => 'Mie Goreng',
            'description' => 'Mie goreng hangat siap disajikan',
            'price' => 12000,
            'image' => null,
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $snack->id,
            'name' => 'Roti Cokelat',
            'description' => 'Roti dengan isian cokelat',
            'price' => 8000,
            'image' => null,
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $snack->id,
            'name' => 'Kentang Goreng',
            'description' => 'Kentang goreng hangat',
            'price' => 10000,
            'image' => null,
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $minuman->id,
            'name' => 'Teh Manis',
            'description' => 'Teh manis siap minum',
            'price' => 5000,
            'image' => null,
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $minuman->id,
            'name' => 'Kopi Susu',
            'description' => 'Kopi susu hangat',
            'price' => 10000,
            'image' => null,
            'is_available' => true,
        ]);
    }
}