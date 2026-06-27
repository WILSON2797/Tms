<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentOrderPhoto extends Model
{
    protected $table = 'shipment_order_photos';

    protected $fillable = [
        'shipment_order_id',
        'photo_path'
    ];

    public function shipmentOrder()
    {
        return $this->belongsTo(ShipmentOrder::class, 'shipment_order_id');
    }
}
