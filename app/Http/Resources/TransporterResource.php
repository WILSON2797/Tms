<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransporterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transporter_code' => $this->transporter_code,
            'transporter_name' => $this->transporter_name,
            'address' => $this->address,
            'pic_name' => $this->pic_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
