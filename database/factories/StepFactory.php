<?php

namespace Database\Factories;

use App\Models\Choice;
use App\Models\Step;
use App\QuestionType;
use App\StepType;
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
        $stepType = $this->faker->randomElement(StepType::cases());

        return match ($stepType) {
            StepType::Question => [
                'title' => $this->faker->sentence(),
                'content' => $this->faker->paragraph(),
                'step_type' => $stepType,
                'question_type' => $this->faker->randomElement(QuestionType::cases()),
            ],
            StepType::Dialog => [
                'title' => $this->faker->sentence(),
                'content' => $this->faker->paragraph(),
                'step_type' => $stepType,
                'question_type' => null,
            ],
        };
    }
}
