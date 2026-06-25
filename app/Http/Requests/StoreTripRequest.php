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
            'driver_id' => 'nullable|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'status' => 'nullable|string|in:DRAFT,IN_TRANSIT,DELIVERED,CANCELLED',
            'shipment_order_ids' => 'required|array|min:1',
            'shipment_order_ids.*' => 'exists:shipment_orders,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $orderIds = $this->input('shipment_order_ids', []);
            if (count($orderIds) > 1) {
                $citiesCount = \App\Models\ShipmentOrder::whereIn('id', $orderIds)
                    ->distinct()
                    ->count('destination_city');

                if ($citiesCount > 1) {
                    $validator->errors()->add(
                        'shipment_order_ids',
                        'Semua Shipment Order dalam satu trip harus memiliki kota tujuan (Destination City) yang sama.'
                    );
                }
            }

            $modId = $this->input('mod_id');
            if ($modId) {
                $mod = \App\Models\ModeOfDelivery::find($modId);
                if ($mod) {
                    $isConsoleOrMultidrop = (
                        stripos($mod->code, 'console') !== false ||
                        stripos($mod->name, 'console') !== false ||
                        stripos($mod->code, 'multidrop') !== false ||
                        stripos($mod->name, 'multidrop') !== false
                    );

                    if (count($orderIds) > 1 && !$isConsoleOrMultidrop) {
                        $validator->errors()->add(
                            'mod_id',
                            'Untuk pengiriman lebih dari 1 order, Mode of Delivery (MOD) harus bertipe Console atau Multidrop.'
                        );
                    }

                    if (count($orderIds) === 1 && $isConsoleOrMultidrop) {
                        $validator->errors()->add(
                            'mod_id',
                            'Untuk pengiriman 1 order, Mode of Delivery (MOD) tidak boleh bertipe Console atau Multidrop.'
                        );
                    }
                }
            }
        });
    }
}
