<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $fillable = [
        'trip_number',
        'trip_date',
        'mot_id',
        'mod_id',
        'transporter_id',
        'driver_id',
        'vehicle_id',
        'status',
        'current_latitude',
        'current_longitude',
        'created_by'
    ];

    protected $casts = [
        'trip_date' => 'date:Y-m-d',
        'current_latitude' => 'float',
        'current_longitude' => 'float',
    ];

    /**
     * Get the GPS location logs for this trip.
     */
    public function locationLogs(): HasMany
    {
        return $this->hasMany(TripLocationLog::class);
    }

    /**
     * Get the shipment orders assigned to this trip.
     */
    public function shipmentOrders(): HasMany
    {
        return $this->hasMany(ShipmentOrder::class);
    }

    /**
     * Get the Mode of Transport.
     */
    public function modeOfTransport(): BelongsTo
    {
        return $this->belongsTo(ModeOfTransport::class, 'mot_id');
    }

    /**
     * Get the Mode of Delivery.
     */
    public function modeOfDelivery(): BelongsTo
    {
        return $this->belongsTo(ModeOfDelivery::class, 'mod_id');
    }

    /**
     * Get the transporter assigned to the trip.
     */
    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class);
    }

    /**
     * Get the driver assigned to the trip.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the vehicle assigned to the trip.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who created the trip.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
