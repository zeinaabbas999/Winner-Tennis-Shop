<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'brand' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'specs' => ['nullable', 'array'],
            'specs.weight' => ['nullable', 'string', 'max:50'],
            'specs.head_size' => ['nullable', 'string', 'max:50'],
            'specs.balance' => ['nullable', 'string', 'max:50'],
            'specs.string_pattern' => ['nullable', 'string', 'max:50'],
            'specs.skill_level' => ['nullable', 'string', 'max:100'],
            'specs.play_style' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
