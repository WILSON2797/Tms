<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentStatusLog extends Model
{
    // Disable default timestamps since we only use created_at
    public $timestamps = false;

    protected $fillable = [
        'shipment_order_id',
        'status',
        'description',
        'changed_by'
    ];

    /**
     * Get the shipment order associated with the log.
     */
    public function shipmentOrder(): BelongsTo
    {
        return $this->belongsTo(ShipmentOrder::class);
    }

    /**
     * Get the user who changed the status.
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
