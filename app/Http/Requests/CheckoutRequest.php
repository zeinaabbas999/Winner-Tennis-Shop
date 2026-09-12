<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checkout_token' => ['required', 'uuid'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'governorate' => ['required', 'string', 'max:100'],
            'city_area' => ['required', 'string', 'max:150'],
            'street' => ['required', 'string', 'max:255'],
            'building_number' => ['required', 'string', 'max:50'],
            'floor_apartment' => ['nullable', 'string', 'max:100'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
