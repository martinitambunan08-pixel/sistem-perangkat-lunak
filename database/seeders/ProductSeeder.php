<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $makananUtama = ProductCategory::firstOrCreate([
                'name' => 'Makanan Utama',
            ]);

            $snack = ProductCategory::firstOrCreate([
                'name' => 'Snack',
            ]);

            $products = [
                [
                    'category_id' => $makananUtama->id,
                    'name' => 'Mie Tarempa Original',
                    'description' => 'Mie khas Tarempa, Kepulauan Riau, disajikan hangat.',
                    'price' => 12000,
                    'image' => null,
                    'is_available' => true,
                ],
                [
                    'category_id' => $makananUtama->id,
                    'name' => 'Mie Tarempa Ayam',
                    'description' => 'Mie Tarempa dengan ayam, disajikan hangat.',
                    'price' => 15000,
                    'image' => null,
                    'is_available' => true,
                ],
                [
                    'category_id' => $makananUtama->id,
                    'name' => 'Mie Lendir',
                    'description' => 'Mie khas Melayu dengan kuah kacang kental.',
                    'price' => 12000,
                    'image' => null,
                    'is_available' => true,
                ],
                [
                    'category_id' => $makananUtama->id,
                    'name' => 'Nasi Dagang',
                    'description' => 'Hidangan nasi dengan lauk khas Melayu.',
                    'price' => 15000,
                    'image' => null,
                    'is_available' => true,
                ],
                [
                    'category_id' => $snack->id,
                    'name' => 'Luti Gendang Cokelat',
                    'description' => 'Luti Gendang dengan isian cokelat.',
                    'price' => 10000,
                    'image' => null,
                    'is_available' => true,
                ],
                [
                    'category_id' => $snack->id,
                    'name' => 'Luti Gendang Abon',
                    'description' => 'Luti Gendang dengan isian abon.',
                    'price' => 10000,
                    'image' => null,
                    'is_available' => true,
                ],
            ];

            foreach ($products as $product) {
                Product::updateOrCreate(
                    ['name' => $product['name']],
                    $product
                );
            }
        });
    }
}