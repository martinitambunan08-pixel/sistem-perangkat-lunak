<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_inventories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('machine_id')
                ->constrained('machines')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->integer('quantity')->default(0);
            $table->integer('minimum_stock')->default(1);
            $table->integer('maximum_stock')->default(10);

            $table->timestamp('last_restocked_at')->nullable();

            $table->timestamps();

            $table->unique(['machine_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_inventories');
    }
};