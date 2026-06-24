<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShipmentOrder extends Model
{
    protected $fillable = [
        'job_number',
        'order_number',
        'customer_id',
        'order_date',
        'origin_city',
        'destination_city',
        'detail_address',
        'transporter_id',
        'trip_id',
        'recipient_name',
        'recipient_company',
        'expected_delivery_date',
        'order_type',
        'status',
        'pod_recipient_name',
        'pod_photo_path',
        'pod_signature_path',
        'pod_received_at',
        'assigned_at',
        'accepted_at',
        'arrived_at',
        'created_by'
    ];

    protected $casts = [
        'order_date' => 'date:Y-m-d',
        'expected_delivery_date' => 'date:Y-m-d',
        'pod_received_at' => 'datetime:Y-m-d H:i:s',
        'assigned_at' => 'datetime:Y-m-d H:i:s',
        'accepted_at' => 'datetime:Y-m-d H:i:s',
        'arrived_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Get the customer that placed the order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the transporter assigned to the order.
     */
    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class);
    }

    /**
     * Get the trip associated with the shipment order.
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get the status history logs for this order.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(ShipmentStatusLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the user who created the order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
