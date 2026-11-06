<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-catalog') ?? false;
    }

    public function rules(): array
    {
        $parent = $this->input('category_id');
        $name = Rule::unique('subcategories', 'name')->where('category_id', is_scalar($parent) ? $parent : null);
        if ($subcategory = $this->route('subcategory')) {
            $name->ignore($subcategory);
        }

        return [
            'category_id' => ['bail', 'required', 'integer', 'exists:categories,id'],
            'name' => ['bail', 'required', 'string', 'max:120', $name],
        ];
    }
}
