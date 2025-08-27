<?php

namespace Database\Factories;

use App\Models\Response;
use App\Models\Step;
use App\QuestionType;
use App\ResponseType;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;

/**
 * @extends Factory<Response>
 */
class ResponseFactory extends Factory
{
    public function definition(): array
    {
        // Default (should rarely be used — we normally call ->forStep($step))
        return [
            'type' => QuestionType::Text,
            'value' => $this->faker->sentence(),
        ];
    }

    /**
     * State helper: set the Response's type/value based on the provided Step's question_type.
     */
    public function forStep(Step $step): static
    {
        return $this->state(function () use ($step) {
            $qt = $step->question_type;

            // Fallback to a simple text if no question type is set.
            if (!$qt) {
                return [
                    'type' => QuestionType::Text,
                    'value' => $this->faker->sentence(),
                ];
            }

            switch ($qt) {

                case QuestionType::Number:
                    return [
                        'type' => QuestionType::Number,
                        'value' => $this->faker->randomFloat(2, 0, 100),
                    ];

                case QuestionType::Date:
                    return [
                        'type' => QuestionType::Date,
                        'value' => $this->faker->date(),
                    ];

                case QuestionType::SingleChoice:
                    // Store the chosen option as text (using the choice id as a safe fallback)
                    $choiceId = (string)($step->choices()->inRandomOrder()->value('id') ?? $this->faker->numberBetween(1, 5));
                    return [
                        'type' => QuestionType::SingleChoice,
                        'value' => $choiceId, // or swap to a label if you have one, e.g. ->value('label')
                    ];

                case QuestionType::MultipleChoice:
                    // Fetch full choice objects to store in the response
                    $choices = $step->choices()
                        ->get(['id', 'step_id', 'label', 'value', 'order', 'optional_next_step']);

                    if ($choices->isEmpty()) {
                        throw new InvalidArgumentException('Cannot build Response: MultipleChoice step has no choices.');
                    }

                    // Select at least 2 if available; otherwise 1
                    $count = $choices->count();
                    $take  = $count >= 2 ? $this->faker->numberBetween(2, $count) : 1;

                    $selected = $choices->shuffle()->take($take)->values();

                    return [
                        // Store JSON string of the selected choices
                        'type'  => QuestionType::MultipleChoice,
                        'value' => $selected->toJson(),
                    ];

                default:
                    return [
                        'type' => QuestionType::Text,
                        'value' => $this->faker->sentence(),
                    ];
            }
        });
    }
}
