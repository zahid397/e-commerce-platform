<?php

namespace App\Http\Requests;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-catalog') ?? false;
    }

    protected function prepareForValidation(): void
    {
        // An omitted optional price is cleared, so a prior discount cannot
        // survive a price change that did not validate it.
        if (! $this->has('old_price')) {
            $this->merge(['old_price' => null]);
        }
    }

    public function rules(): array
    {
        $parent = $this->input('category_id');

        return [
            'category_id' => ['bail', 'required', 'integer', 'exists:categories,id'],
            'subcategory_id' => [
                'bail', 'required', 'integer',
                Rule::exists('subcategories', 'id')->where('category_id', is_scalar($parent) ? $parent : null),
            ],
            'name' => ['bail', 'required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:6000'],
            'old_price' => ['nullable', 'numeric', Money::INPUT_RULE],
            'new_price' => ['required', 'numeric', Money::INPUT_RULE],
            'is_active' => ['required', 'boolean'],
            'image' => [
                'bail', $this->route('product') ? 'nullable' : 'required', 'file', 'image',
                'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:3072',
                'dimensions:max_width=3200,max_height=3200',
            ],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['old_price', 'new_price']) || ! $this->filled('old_price')) {
                return;
            }

            if (Money::minor((string) $this->input('old_price')) < Money::minor((string) $this->input('new_price'))) {
                $validator->errors()->add('old_price', 'Old price must be at least the new price.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'subcategory_id.exists' => 'Choose a subcategory that belongs to the selected category.',
            'image.image' => 'Upload a valid JPEG, PNG or WebP image.',
            'image.max' => 'The product image must be no larger than 3 MB.',
            'image.dimensions' => 'The product image must be at most 3,200 × 3,200 pixels.',
            'old_price.regex' => 'Enter a nonnegative price with at most two decimal places.',
            'new_price.regex' => 'Enter a nonnegative price with at most two decimal places.',
        ];
    }
}
