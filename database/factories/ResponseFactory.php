<?php

namespace Database\Factories;

use App\Models\Response;
use App\ResponseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Response>
 */
class ResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $responseType = $this->faker->randomElement(ResponseType::cases());

        return match ($responseType) {
            ResponseType::Text => [
                'value' => $this->faker->sentence(),
                'type' => $responseType,
            ],
            ResponseType::Number => [
                'value' => $this->faker->randomFloat(2, 0, 100),
                'type' => $responseType,
            ],
            ResponseType::Boolean => [
                'value' => $this->faker->boolean(),
                'type' => $responseType,
            ],
            ResponseType::Date => [
                'value' => $this->faker->date(),
                'type' => $responseType,
            ]
        };
    }
}
