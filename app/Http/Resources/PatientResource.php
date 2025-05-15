<?php

namespace App\Http\Resources;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Patient
 */
class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => sprintf('%s, %s', $this->last_name, $this->first_name),
            'email' => $this->email,
            'birth_date' => $this->birth_date->format('d.m.Y'),
            'gender' => $this->gender,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone,
            'insurance_name' => $this->insurance_name,
            'insurance_number' => $this->insurance_number,
            'insurance_type' => $this->insurance_type,
            'occupation' => $this->occupation,
            'emergency_contact' => $this->emergency_contact,
        ];
    }
}
