<?php

namespace App\Http\Requests;

use DateTime;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSurveyRequest extends FormRequest
{
    /**
     * Allow the request (gate it higher up if needed).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Set JSON-Schema defaults (version=1, is_active=true) when missing.
     */
    protected function prepareForValidation(): void
    {
        $survey = (array) ($this->input('survey') ?? []);

        if (! array_key_exists('version', $survey) || $survey['version'] === null) {
            $survey['version'] = 1;
        }

        if (! array_key_exists('is_active', $survey) || $survey['is_active'] === null) {
            $survey['is_active'] = true;
        }

        $this->merge(['survey' => $survey]);
    }

    /**
     * Base rules (type/shape). Conditional parts are enforced in withValidator().
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // survey object
            'survey' => ['required', 'array'],
            'survey.name' => ['required', 'string', 'min:1'],
            'survey.description' => ['nullable', 'string'],
            'survey.version' => ['required', 'integer', 'min:1'],
            'survey.is_active' => ['required', 'boolean'],

            // steps
            'survey.steps' => ['required', 'array', 'min:1'],
            'survey.steps.*' => ['required', 'array'],
            'survey.steps.*.order' => ['required', 'integer', 'min:1'],
            'survey.steps.*.title' => ['nullable', 'string'],
            'survey.steps.*.content' => ['nullable', 'string'],

            'survey.steps.*.step_type' => ['required', 'string', Rule::in(['info', 'question'])],
            'survey.steps.*.question_type' => ['nullable', 'string', Rule::in(['single_choice', 'multiple_choice', 'text', 'number', 'date'])],
            'survey.steps.*.options' => ['nullable', 'array'],
            'survey.steps.*.choices' => ['nullable', 'array', 'min:1'],

            // choices[*]
            'survey.steps.*.choices.*' => ['required', 'array'],
            'survey.steps.*.choices.*.label' => ['required', 'string', 'min:1'],
            'survey.steps.*.choices.*.value' => ['required', 'string', 'min:1'],
            'survey.steps.*.choices.*.next_step' => ['nullable', 'integer', 'min:1'],
            'survey.steps.*.choices.*.order' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Add conditional logic to mirror JSON Schema "if/then/else" and
     * enforce "additionalProperties: false" for objects.
     */
    public function withValidator(Validator $validator): void
    {
        $steps = (array) data_get($this->all(), 'survey.steps', []);

        foreach ($steps as $i => $step) {
            $stepType = data_get($step, 'step_type');
            $qType = data_get($step, 'question_type');

            // When step_type = question => question_type is required
            if ($stepType === 'question') {
                $validator->sometimes("survey.steps.$i.question_type", 'required', fn () => true);

                // Choices vs options per question_type
                if (in_array($qType, ['single_choice', 'multiple_choice'], true)) {
                    // choices required
                    $validator->sometimes("survey.steps.$i.choices", 'required|array|min:1', fn () => true);

                    // options schema for choice questions
                    $validator->sometimes("survey.steps.$i.options.min_choices", 'nullable|integer|min:0', fn () => true);
                    $validator->sometimes("survey.steps.$i.options.max_choices", 'nullable|integer|min:1', fn () => true);
                    $validator->sometimes("survey.steps.$i.options.allow_other", 'nullable|boolean', fn () => true);
                    $validator->sometimes("survey.steps.$i.options.other_label", 'nullable|string', fn () => true);
                    $validator->sometimes("survey.steps.$i.options.optional", 'nullable|boolean', fn () => true);
                }

                if ($qType === 'text') {
                    // choices prohibited; text options
                    $validator->sometimes("survey.steps.$i.choices", 'prohibited', fn () => true);

                    $validator->sometimes("survey.steps.$i.options.placeholder", 'nullable|string', fn () => true);
                    $validator->sometimes("survey.steps.$i.options.max_length", 'nullable|integer|min:1', fn () => true);
                    $validator->sometimes("survey.steps.$i.options.optional", 'nullable|boolean', fn () => true);
                }

                if ($qType === 'number') {
                    // choices prohibited; number options
                    $validator->sometimes("survey.steps.$i.choices", 'prohibited', fn () => true);

                    $validator->sometimes("survey.steps.$i.options.min", 'nullable|numeric', fn () => true);
                    $validator->sometimes("survey.steps.$i.options.max", 'nullable|numeric', fn () => true);
                    $validator->sometimes("survey.steps.$i.options.step", 'nullable|numeric', fn () => true);
                    $validator->sometimes("survey.steps.$i.options.optional", 'nullable|boolean', fn () => true);
                }

                if ($qType === 'date') {
                    // choices prohibited; date options
                    $validator->sometimes("survey.steps.$i.choices", 'prohibited', fn () => true);

                    // min/max: allow literal 'today' OR YYYY-MM-DD
                    $dateRule = function (string $attribute, $value, $fail) {
                        if ($value === null || $value === '') {
                            return;
                        }

                        if ($value === 'today') {
                            return;
                        }

                        // Fully qualify to avoid namespace issues, or add "use DateTime;" at the top
                        $dt = \DateTime::createFromFormat('Y-m-d', (string) $value);

                        $errors = \DateTime::getLastErrors();
                        $warningCount = is_array($errors) ? ($errors['warning_count'] ?? 0) : 0;
                        $errorCount = is_array($errors) ? ($errors['error_count'] ?? 0) : 0;

                        if (! $dt || $warningCount > 0 || $errorCount > 0) {
                            $fail("The $attribute must be 'today' or a valid date in Y-m-d format.");
                        }
                    };

                    $validator->sometimes("survey.steps.$i.options.min", ['nullable', $dateRule], fn () => true);
                    $validator->sometimes("survey.steps.$i.options.max", ['nullable', $dateRule], fn () => true);

                    // format: enum ['YYYY-MM-DD', null]
                    $validator->sometimes("survey.steps.$i.options.format", ['nullable', Rule::in(['YYYY-MM-DD'])], fn () => true);
                    $validator->sometimes("survey.steps.$i.options.optional", 'nullable|boolean', fn () => true);
                }
            } else { // step_type = info
                // For info steps, these must be null/absent (schema "else")
                $validator->sometimes("survey.steps.$i.question_type", 'prohibited', fn () => true);
                $validator->sometimes("survey.steps.$i.options", 'prohibited', fn () => true);
                $validator->sometimes("survey.steps.$i.choices", 'prohibited', fn () => true);
            }
        }

        // Enforce "additionalProperties: false" (best-effort) for objects
        $validator->after(function (Validator $v) use ($steps) {
            $data = (array) $this->input();

            // Allowed keys at each object level per schema
            $allowedSurvey = ['name', 'description', 'version', 'is_active', 'steps'];
            $allowedStep = ['order', 'title', 'content', 'step_type', 'question_type', 'options', 'choices'];
            $allowedChoice = ['label', 'value', 'next_step', 'order'];

            $choiceOptionKeys = ['min_choices', 'max_choices', 'allow_other', 'other_label', 'optional'];
            $textOptionKeys = ['placeholder', 'max_length', 'optional'];
            $numberOptionKeys = ['min', 'max', 'step', 'optional'];
            $dateOptionKeys = ['min', 'max', 'format', 'optional'];

            // survey additional props
            $survey = (array) data_get($data, 'survey', []);
            $extraSurvey = array_diff(array_keys($survey), $allowedSurvey);
            if ($extraSurvey) {
                $v->errors()->add('survey', 'Unknown keys: '.implode(', ', $extraSurvey));
            }

            // steps and nested objects
            foreach ($steps as $i => $step) {
                // step additional props
                $extraStep = array_diff(array_keys((array) $step), $allowedStep);
                if ($extraStep) {
                    $v->errors()->add("survey.steps.$i", 'Unknown keys: '.implode(', ', $extraStep));
                }

                // options additional props based on question_type
                $qType = data_get($step, 'question_type');
                $opts = (array) data_get($step, 'options', []);
                if ($opts !== []) {
                    $allowed = match ($qType) {
                        'single_choice', 'multiple_choice' => $choiceOptionKeys,
                        'text' => $textOptionKeys,
                        'number' => $numberOptionKeys,
                        'date' => $dateOptionKeys,
                        default => [], // if not a question or missing type, options are prohibited anyway
                    };
                    if ($allowed !== []) {
                        $extraOpts = array_diff(array_keys($opts), $allowed);
                        if ($extraOpts) {
                            $v->errors()->add("survey.steps.$i.options", 'Unknown keys: '.implode(', ', $extraOpts));
                        }
                    }
                }

                // choices additional props
                $choices = data_get($step, 'choices');
                if (is_array($choices)) {
                    foreach ($choices as $j => $choice) {
                        $extraChoice = array_diff(array_keys((array) $choice), $allowedChoice);
                        if ($extraChoice) {
                            $v->errors()->add("survey.steps.$i.choices.$j", 'Unknown keys: '.implode(', ', $extraChoice));
                        }
                    }
                }
            }
        });
    }

    /**
     * Optional: friendlier messages for common mistakes.
     */
    public function messages(): array
    {
        return [
            'survey.required' => 'The survey payload is required.',
            'survey.name.required' => 'Survey name is required.',
            'survey.steps.required' => 'At least one step is required.',
            'survey.steps.min' => 'Provide at least one step.',
            'survey.steps.*.order.min' => 'Step order must be >= 1.',
            'survey.steps.*.step_type.in' => 'step_type must be either info or question.',
            'survey.steps.*.question_type.in' => 'Invalid question_type.',
            'survey.steps.*.choices.min' => 'Provide at least one choice.',
            'survey.steps.*.choices.*.label.min' => 'Choice label cannot be empty.',
            'survey.steps.*.choices.*.value.min' => 'Choice value cannot be empty.',
        ];
    }
}
