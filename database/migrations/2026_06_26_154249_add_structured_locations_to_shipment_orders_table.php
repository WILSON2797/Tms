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
        Schema::table('shipment_orders', function (Blueprint $table) {
            $table->string('origin_province')->nullable()->after('origin_city');
            $table->string('origin_district')->nullable()->after('origin_province');
            $table->string('destination_province')->nullable()->after('destination_city');
            $table->string('destination_district')->nullable()->after('destination_province');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_orders', function (Blueprint $table) {
            $table->dropColumn([
                'origin_province',
                'origin_district',
                'destination_province',
                'destination_district'
            ]);
        });
    }
};
