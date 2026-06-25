<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_number' => 'required|string|unique:shipment_orders,order_number',
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'origin_city' => 'required|string',
            'destination_city' => 'required|string',
            'detail_address' => 'required|string',
            'transporter_id' => 'nullable|exists:transporters,id',
            'recipient_name' => 'required|string',
            'recipient_company' => 'required|string',
            'expected_delivery_date' => 'required|date',
            'order_type' => 'required|string|in:REGULAR,URGENT',
            'status' => 'nullable|string|in:DRAFT,ASSIGNED,IN_TRANSIT,DELIVERED,CANCELLED',
        ];
    }

    public function messages(): array
    {
        return [
            'order_number.unique' => 'Nomor order / shipment customer sudah terdaftar di sistem (tidak boleh duplikat).',
        ];
    }
}
