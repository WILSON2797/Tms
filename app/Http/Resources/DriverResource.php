<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'driver_code' => $this->driver_code ?? ('DRV-' . str_pad($this->id, 3, '0', STR_PAD_LEFT)),
            'driver_name' => $this->driver_name ?? $this->name,
            'phone' => $this->phone ?? null,
            'sim_number' => $this->sim_number ?? null,
            'sim_expired' => isset($this->sim_expired) ? ($this->sim_expired instanceof \Carbon\Carbon ? $this->sim_expired->toDateString() : $this->sim_expired) : null,
            'license_type' => $this->license_type ?? null,
            'address' => $this->address ?? null,
            'photo' => $this->photo ?? null,
            'is_active' => (bool) ($this->is_active ?? true),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
