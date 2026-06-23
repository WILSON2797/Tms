<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'driver_code' => 'required|string|unique:drivers,driver_code',
            'driver_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'sim_number' => 'nullable|string|max:50',
            'sim_expired' => 'nullable|date',
            'license_type' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'photo_file' => 'nullable|image|max:2048', // max 2MB
            'is_active' => 'nullable|boolean',
        ];
    }
}
