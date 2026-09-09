<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $this->user()?->hasPurchasedProduct($product) === true;
    }

    public function rules(): array
    {
        return [
            'rating' => [
                'required',
                'integer',
                'between:1,5',
            ],
            'comment' => ['nullable', 'string', 'max:1500'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (Review::whereBelongsTo($this->user())->whereBelongsTo($this->route('product'))->exists()) {
                $validator->errors()->add('rating', 'You have already reviewed this product.');
            }
        }];
    }
}
