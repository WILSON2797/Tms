<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripLocationLog extends Model
{
    // Disable default timestamps since we only use created_at
    public $timestamps = false;

    protected $fillable = [
        'trip_id',
        'latitude',
        'longitude'
    ];

    /**
     * Get the trip associated with this location log.
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
