<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trip_number' => $this->trip_number,
            'trip_date' => $this->trip_date?->format('Y-m-d'),
            'mot_id' => $this->mot_id,
            'mot' => $this->modeOfTransport ? $this->modeOfTransport->name : null,
            'mod_id' => $this->mod_id,
            'mod' => $this->modeOfDelivery ? $this->modeOfDelivery->name : null,
            'transporter_id' => $this->transporter_id,
            'transporter' => new TransporterResource($this->whenLoaded('transporter')),
            'driver_id' => $this->driver_id,
            'driver' => $this->whenLoaded('driver', function () {
                return [
                    'id' => $this->driver->id,
                    'driver_name' => $this->driver->name,
                    'username' => $this->driver->username,
                ];
            }),
            'vehicle_id' => $this->vehicle_id,
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'status' => $this->status,
            'current_latitude' => $this->current_latitude,
            'current_longitude' => $this->current_longitude,
            // Mobile app task aliases
            'task_id' => $this->id,
            'task_number' => $this->trip_number,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'username' => $this->creator->username,
                ];
            }),
            'shipment_orders' => ShipmentOrderResource::collection($this->whenLoaded('shipmentOrders')),
            'location_logs' => $this->whenLoaded('locationLogs', function () {
                return $this->locationLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'latitude' => $log->latitude,
                        'longitude' => $log->longitude,
                        'created_at' => $log->created_at,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
