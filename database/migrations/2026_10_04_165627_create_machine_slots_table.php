<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_slots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('machine_id')
                ->constrained('machines')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('slot_number');
            $table->integer('capacity')->default(10);
            $table->integer('current_stock')->default(0);

            $table->enum('status', [
                'active',
                'empty',
                'disabled'
            ])->default('active');

            $table->timestamps();

            $table->unique(['machine_id', 'slot_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_slots');
    }
};