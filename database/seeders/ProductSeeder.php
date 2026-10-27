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

        Product::create([
            'category_id' => $makananUtama->id,
            'name' => 'Mie Tarempa Original',
            'description' => 'Mie Tarempa Original hangat siap disajikan',
            'price' => 12000,
            'image' => null,
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $makananUtama->id,
            'name' => 'Mie Tarempa Ayam',
            'description' => 'Mie Tarempa Ayam hangat siap disajikan',
            'price' => 15000,
            'image' => null,
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $snack->id,
            'name' => 'Luti Gendang Cokelat',
            'description' => 'Luti Gendang dengan isian cokelat',
            'price' => 10000,
            'image' => null,
            'is_available' => true,
        ]);

        Product::create([
            'category_id' => $snack->id,
            'name' => 'Luti Gendang Abon',
            'description' => 'Luti Gendang dengan isian abon',
            'price' => 10000,
            'image' => null,
            'is_available' => true,
        ]);
    }
}