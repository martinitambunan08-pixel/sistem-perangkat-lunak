<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        ProductCategory::create([
            'name' => 'Makanan Utama',
            'description' => 'Makanan panas siap saji',
            'is_active' => true,
        ]);

        ProductCategory::create([
            'name' => 'Snack',
            'description' => 'Makanan ringan',
            'is_active' => true,
        ]);

        ProductCategory::create([
            'name' => 'Minuman',
            'description' => 'Minuman siap konsumsi',
            'is_active' => true,
        ]);
    }
}