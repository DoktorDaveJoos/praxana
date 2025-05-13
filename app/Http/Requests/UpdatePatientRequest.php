<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'birth_date' => 'sometimes|date',
            'gender' => 'in:male,female,divers',
            'address' => 'string|max:255',
            'postal_code' => 'string|max:20',
            'city' => 'string|max:255',
            'country' => 'string|max:255',
            'phone' => 'string|max:255',
            'email' => 'email|max:255',
            'occupation' => 'string|max:255',
            'insurance_type' => 'in:private,public',
            'insurance_name' => 'string|max:255',
            'insurance_number' => 'string|max:255',
            'emergency_contact' => 'string|max:255',
        ];
    }
}
