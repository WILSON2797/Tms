<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'cities';

    protected $fillable = [
        'name',
        'district',
        'type',
        'province',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
