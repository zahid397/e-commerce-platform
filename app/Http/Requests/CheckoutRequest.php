<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'checkout_token' => ['required', 'uuid'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'phone' => ['required', 'string', 'regex:/\A\+?[0-9][0-9 ()-]{6,23}\z/'],
            'address' => ['required', 'string', 'min:8', 'max:500'],
        ];
    }
}
