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
            'specs.strung_weight' => ['nullable', 'string', 'max:50'],
            'specs.length' => ['nullable', 'string', 'max:50'],
            'specs.beam_width' => ['nullable', 'string', 'max:50'],
            'specs.composition' => ['nullable', 'string', 'max:100'],
            'specs.grip_size' => ['nullable', 'string', 'max:100'],
            'specs.stiffness' => ['nullable', 'string', 'max:50'],
            'specs.ball_type' => ['nullable', 'string', 'max:100'],
            'specs.balls_per_can' => ['nullable', 'string', 'max:50'],
            'specs.court_surface' => ['nullable', 'string', 'max:100'],
            'specs.pressure' => ['nullable', 'string', 'max:50'],
            'specs.felt_type' => ['nullable', 'string', 'max:100'],
            'specs.competition_level' => ['nullable', 'string', 'max:100'],
            'specs.racquet_capacity' => ['nullable', 'string', 'max:50'],
            'specs.bag_type' => ['nullable', 'string', 'max:100'],
            'specs.compartments' => ['nullable', 'string', 'max:50'],
            'specs.shoe_compartment' => ['nullable', 'string', 'max:50'],
            'specs.thermal_protection' => ['nullable', 'string', 'max:50'],
            'specs.dimensions' => ['nullable', 'string', 'max:100'],
            'specs.material' => ['nullable', 'string', 'max:100'],
            'specs.straps' => ['nullable', 'string', 'max:100'],
            'specs.string_type' => ['nullable', 'string', 'max:100'],
            'specs.gauge' => ['nullable', 'string', 'max:50'],
            'specs.pack_length' => ['nullable', 'string', 'max:50'],
            'specs.construction' => ['nullable', 'string', 'max:100'],
            'specs.playing_character' => ['nullable', 'string', 'max:100'],
            'specs.grip_type' => ['nullable', 'string', 'max:100'],
            'specs.thickness' => ['nullable', 'string', 'max:50'],
            'specs.pack_size' => ['nullable', 'string', 'max:50'],
            'specs.feel' => ['nullable', 'string', 'max:100'],
            'specs.absorbency' => ['nullable', 'string', 'max:100'],
            'specs.color' => ['nullable', 'string', 'max:50'],
            'specs.dampener_type' => ['nullable', 'string', 'max:100'],
            'specs.shape_design' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'new_badge_mode' => ['sometimes', Rule::in(['auto', 'force_on', 'force_off'])],
        ];
    }
}
