<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_menu_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('daily_menu_id')
                ->constrained('daily_menus')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->unsignedInteger('initial_portions');
            $table->unsignedInteger('remaining_portions');

            $table->timestamps();

            $table->unique(
                ['daily_menu_id', 'product_id'],
                'daily_menu_items_menu_product_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_menu_items');
    }
};