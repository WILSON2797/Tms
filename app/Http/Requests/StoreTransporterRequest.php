<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransporterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transporter_code' => 'required|string|unique:transporters,transporter_code',
            'transporter_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'pic_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'is_active' => 'nullable|boolean',
        ];
    }
}
