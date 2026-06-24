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
            $table->timestamp('assigned_at')->nullable()->after('status');
            $table->timestamp('accepted_at')->nullable()->after('assigned_at');
            $table->timestamp('arrived_at')->nullable()->after('accepted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_orders', function (Blueprint $table) {
            $table->dropColumn(['assigned_at', 'accepted_at', 'arrived_at']);
        });
    }
};
