<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heater_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('machine_id')
                ->unique()
                ->constrained('machines')
                ->cascadeOnDelete();

            $table->decimal('target_temperature', 5, 2)->default(62.00);
            $table->decimal('minimum_temperature', 5, 2)->default(60.00);
            $table->decimal('maximum_temperature', 5, 2)->default(65.00);

            $table->boolean('heater_status')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heater_settings');
    }
};