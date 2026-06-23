<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModeOfTransport extends Model
{
    protected $table = 'modes_of_transport';

    protected $fillable = [
        'code',
        'name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
