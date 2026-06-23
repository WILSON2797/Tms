<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_no')->unique();
            $table->string('vehicle_type');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->decimal('capacity_weight', 10, 2)->default(0);
            $table->decimal('capacity_volume', 10, 2)->default(0);
            $table->enum('ownership', ['internal', 'external'])->default('internal');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
