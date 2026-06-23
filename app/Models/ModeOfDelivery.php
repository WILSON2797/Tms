<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModeOfDelivery extends Model
{
    protected $table = 'modes_of_delivery';

    protected $fillable = [
        'code',
        'name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
