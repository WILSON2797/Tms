<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'vehicle_no',
        'vehicle_type',
        'brand',
        'model',
        'capacity_weight',
        'capacity_volume',
        'ownership',
        'is_active',
    ];

    protected $casts = [
        'capacity_weight' => 'decimal:2',
        'capacity_volume' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
