<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_no' => $this->vehicle_no,
            'vehicle_type' => $this->vehicle_type,
            'brand' => $this->brand,
            'model' => $this->model,
            'capacity_weight' => (float) $this->capacity_weight,
            'capacity_volume' => (float) $this->capacity_volume,
            'ownership' => $this->ownership,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
