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
        Schema::create('shipment_orders', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();
            $table->string('order_number');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->date('order_date');
            $table->string('origin_city');
            $table->string('destination_city');
            $table->text('detail_address');
            $table->foreignId('transporter_id')->nullable()->constrained('transporters')->nullOnDelete();
            
            // Recipient & Delivery Info
            $table->string('recipient_name');
            $table->string('recipient_company');
            $table->date('expected_delivery_date');
            $table->string('order_type'); // REGULAR, URGENT
            
            $table->string('status')->default('DRAFT'); // DRAFT, ASSIGNED, IN_TRANSIT, DELIVERED, CANCELLED
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            // Indexes for query optimization
            $table->index('status');
            $table->index('order_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_orders');
    }
};
