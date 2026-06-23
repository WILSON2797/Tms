<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('customer');
        return [
            'customer_code' => 'required|string|unique:customers,customer_code,' . $id,
            'customer_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'pic_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'is_active' => 'nullable|boolean',
        ];
    }
}
