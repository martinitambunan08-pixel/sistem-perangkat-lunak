<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispense_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->restrictOnDelete();

            $table->foreignId('machine_id')
                ->constrained('machines')
                ->restrictOnDelete();

            $table->foreignId('machine_slot_id')
                ->constrained('machine_slots')
                ->restrictOnDelete();

            $table->integer('quantity');
            $table->string('status')->default('pending');
            $table->timestamp('dispensed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispense_logs');
    }
};