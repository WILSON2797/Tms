<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trip_date' => 'required|date',
            'mot_id' => 'required|exists:modes_of_transport,id',
            'mod_id' => 'required|exists:modes_of_delivery,id',
            'transporter_id' => 'nullable|exists:transporters,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'status' => 'nullable|string|in:DRAFT,IN_TRANSIT,DELIVERED,CANCELLED',
            'shipment_order_ids' => 'required|array|min:1',
            'shipment_order_ids.*' => 'exists:shipment_orders,id',
        ];
    }
}
