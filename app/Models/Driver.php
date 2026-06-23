<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'driver_code',
        'driver_name',
        'phone',
        'sim_number',
        'sim_expired',
        'license_type',
        'address',
        'photo',
        'is_active',
    ];

    protected $casts = [
        'sim_expired' => 'date',
        'is_active' => 'boolean',
    ];
}
