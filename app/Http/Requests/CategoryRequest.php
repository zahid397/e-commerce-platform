<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-catalog') ?? false;
    }

    public function rules(): array
    {
        $name = Rule::unique('categories', 'name');
        if ($category = $this->route('category')) {
            $name->ignore($category);
        }

        return [
            'name' => ['bail', 'required', 'string', 'max:120', $name],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
