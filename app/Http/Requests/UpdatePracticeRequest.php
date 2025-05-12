<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePracticeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255'],
            'address' => ['string', 'max:255'],
            'postal_code' => ['string', 'max:20'],
            'city' => ['string', 'max:100'],
            'country' => ['string', 'max:100'],
            'phone' => ['string', 'max:20'],
            'email' => ['email', 'max:255'],
        ];
    }
}
