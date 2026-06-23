<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transporter extends Model
{
    protected $fillable = [
        'transporter_code',
        'transporter_name',
        'address',
        'pic_name',
        'phone',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
