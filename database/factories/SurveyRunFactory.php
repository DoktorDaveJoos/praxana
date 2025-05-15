<?php

namespace Database\Factories;

use App\Models\SurveyRun;
use App\SurveyRunStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyRun>
 */
class SurveyRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => $this->faker->randomElement([SurveyRunStatus::Completed, SurveyRunStatus::Pending]),
            'started_at' => $this->faker->dateTimeThisYear(),
            'finished_at' => $this->faker->dateTimeThisYear(),
            'current_step_id' => null,
        ];
    }
}
