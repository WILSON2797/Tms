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
            $table->string('pod_recipient_name')->nullable()->after('status');
            $table->string('pod_photo_path')->nullable()->after('pod_recipient_name');
            $table->string('pod_signature_path')->nullable()->after('pod_photo_path');
            $table->timestamp('pod_received_at')->nullable()->after('pod_signature_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_orders', function (Blueprint $table) {
            $table->dropColumn(['pod_recipient_name', 'pod_photo_path', 'pod_signature_path', 'pod_received_at']);
        });
    }
};
