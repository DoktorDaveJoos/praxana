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
    public function definition(): array
    {
        $stepType = $this->faker->boolean(80) ? StepType::Question : StepType::Dialog;

        return match ($stepType) {
            StepType::Question => [
                'title' => $this->faker->sentence(),
                'content' => $this->faker->paragraph(),
                'step_type' => $stepType,
                'question_type' => $this->faker->randomElement(QuestionType::cases()),
                'options' => [
                    'optional' => $this->faker->boolean(),
                ],
            ],
            StepType::Dialog => [
                'title' => $this->faker->sentence(),
                'content' => $this->faker->paragraph(),
                'step_type' => $stepType,
                'question_type' => null,
            ],
        };
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Step $step) {
            // Only create choices for question steps of type single/multiple choice
            if (
                $step->step_type === StepType::Question &&
                in_array($step->question_type, [QuestionType::SingleChoice, QuestionType::MultipleChoice], true)
            ) {
                // create 2–6 choices and attach to the step
                Choice::factory()
                    ->count(fake()->numberBetween(2, 6))
                    ->for($step)        // sets step_id
                    ->create();
            }
        });
    }
}
