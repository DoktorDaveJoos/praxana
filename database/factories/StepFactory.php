<?php

namespace Database\Factories;

use App\Models\Step;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Step>
 */
class StepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'step_type' => $this->faker->randomElement(['question', 'info']),
            'question_type' => $this->faker->randomElement(['text', 'multiple_choice', 'single_choice']),
            'default_next_step_id' => null,
        ];
    }
}
