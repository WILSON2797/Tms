<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_number' => $this->job_number,
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'order_date' => $this->order_date?->format('Y-m-d'),
            'origin_city' => $this->origin_city,
            'destination_city' => $this->destination_city,
            'detail_address' => $this->detail_address,
            'transporter_id' => $this->transporter_id,
            'transporter' => new TransporterResource($this->whenLoaded('transporter')),
            'recipient_name' => $this->recipient_name,
            'recipient_company' => $this->recipient_company,
            'expected_delivery_date' => $this->expected_delivery_date?->format('Y-m-d'),
            'order_type' => $this->order_type,
            'status' => $this->status,
            'assigned_at' => $this->assigned_at?->toDateTimeString(),
            'accepted_at' => $this->accepted_at?->toDateTimeString(),
            'arrived_at' => $this->arrived_at?->toDateTimeString(),
            'pod_received_at' => $this->pod_received_at?->toDateTimeString(),
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'username' => $this->creator->username,
                ];
            }),
            'trip' => new TripResource($this->whenLoaded('trip')),
            'status_logs' => $this->whenLoaded('statusLogs', function () {
                return $this->statusLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'status' => $log->status,
                        'description' => $log->description,
                        'changer' => $log->changer ? [
                            'id' => $log->changer->id,
                            'name' => $log->changer->name,
                        ] : null,
                        'created_at' => $log->created_at,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
