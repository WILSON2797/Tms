<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_number' => 'sometimes|required|string',
            'customer_id' => 'sometimes|required|exists:customers,id',
            'order_date' => 'sometimes|required|date',
            'origin_city' => 'sometimes|required|string',
            'destination_city' => 'sometimes|required|string',
            'detail_address' => 'sometimes|required|string',
            'transporter_id' => 'nullable|exists:transporters,id',
            'recipient_name' => 'sometimes|required|string',
            'recipient_company' => 'sometimes|required|string',
            'expected_delivery_date' => 'sometimes|required|date',
            'order_type' => 'sometimes|required|string|in:REGULAR,URGENT',
            'status' => 'sometimes|required|string|in:DRAFT,ASSIGNED,IN_TRANSIT,DELIVERED,CANCELLED',
        ];
    }
}
