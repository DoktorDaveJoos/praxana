<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'birth_date' => $this->faker->date(max: '-30 years'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'address' => $this->faker->address,
            'postal_code' => $this->faker->postcode,
            'city' => $this->faker->city,
            'country' => $this->faker->country,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,
            'occupation' => $this->faker->jobTitle,
            'insurance_type' => $this->faker->randomElement(['private', 'public']),
            'insurance_name' => $this->faker->company,
            'insurance_number' => (string) $this->faker->randomNumber(8),
            'emergency_contact' => $this->faker->phoneNumber,
        ];
    }
}
