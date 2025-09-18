<?php

namespace App\Http\Controllers;

use App\Http\Resources\PatientResource;
use App\Http\Resources\StepResource;
use App\Http\Resources\SurveyResource;
use App\Http\Resources\SurveyRunResource;
use App\Models\Patient;
use App\Models\Practice;
use App\Models\Response;
use App\Models\Step;
use App\Models\SurveyRun;
use App\QuestionType;
use App\StepType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class SurveyStepController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Practice $practice, Patient $patient, SurveyRun $surveyRun, Step $step)
    {
        $this->authorize('view', [SurveyRun::class, $practice, $patient, $surveyRun]);

        $totalSteps = $surveyRun->survey->steps->count();

        $progress = $totalSteps > 1
            ? (int) round((($step->order - 1) / ($totalSteps - 1)) * 100)
            : 100;

        return inertia('patients/survey-runs/steps/Show', [
            'patient' => PatientResource::make($patient),
            'surveyRun' => SurveyRunResource::make($surveyRun),
            'survey' => SurveyResource::make($surveyRun->survey),
            'step' => StepResource::make($step->load('responses')),
            'progress' => $progress,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * Expects payload:
     * {
     *   "answers": [
     *     { "question_id": 123, "value": "2025-08-10" },
     *     { "question_id": 124, "value": 55 },
     *     { "question_id": 125, "value": [201,202] },
     *     { "question_id": 126, "skipped": true }
     *   ],
     *   "meta": { "client_id": "uuid", "time_spent_ms": 1234 }
     * }
     *
     * @throws Throwable
     */
    public function update(
        Practice $practice,
        Patient $patient,
        SurveyRun $surveyRun,
        Step $step,
        Request $request
    ) {
        $this->authorize('update', [SurveyRun::class, $practice, $patient, $surveyRun]);

        // Integrity: step must belong to the survey of this run
        abort_if($step->survey_id !== $surveyRun->survey_id, 404);

        // Dialog step: nothing to validate/store—treat as acknowledged
        if ($step->step_type === StepType::Dialog) {
            return $this->redirectToNextStep(
                $practice,
                $patient,
                $surveyRun,
                $step
            );
        }

        // Question step
        // Determine "required" from options (default false)
        $required = (bool) data_get($step->options, 'required', false);

        // Basic envelope validation (skipped/value handled below)
        $envelope = $request->validate([
            'skipped' => ['sometimes', 'boolean'],
            // don't validate 'value' here; we do it dynamically
        ]);

        $skipped = (bool) ($envelope['skipped'] ?? false);

        // For options/multi we need the available choice IDs
        $choiceIds = collect();
        if ($step->question_type === QuestionType::MultipleChoice ||
            $step->question_type === QuestionType::SingleChoice
        ) {
            $choiceIds = $step->choices()->pluck('id');
        }

        $base = $required ? ['required'] : ['nullable'];

        $rules = match ($step->question_type) {
            QuestionType::Text => array_merge($base, ['string', 'max:10000']),
            QuestionType::Number, QuestionType::Scale => array_merge($base, ['numeric']),
            QuestionType::Date => array_merge($base, ['date_format:Y-m-d']),
            QuestionType::SingleChoice => array_merge($base, [
                'array',
                function ($attribute, $value, $fail) use ($choiceIds) {
                    if (! is_array($value)) {
                        $fail('The selected value must be an object.');

                        return;
                    }

                    if (! array_key_exists('id', $value)) {
                        $fail("The selected value must include an 'id'.");

                        return;
                    }

                    if (! $choiceIds->contains($value['id'])) {
                        $fail("Invalid choice ID: {$value['id']}");
                    }
                },
            ]),
            QuestionType::MultipleChoice => array_merge($base, [
                'array',
                'min:1',
                'max:100',
                'distinct',
                'bail',
                function ($attribute, $value, $fail) use ($choiceIds) {
                    foreach ($value as $index => $item) {
                        if (! isset($item['id']) || ! $choiceIds->contains($item['id'])) {
                            $fail("Invalid choice ID at index $index.");
                        }
                    }
                },
            ]),
            default => throw new InvalidArgumentException(
                "Unsupported question type: $step->question_type"
            ),
        };

        // If explicitly skipped, bypass value validation and store is_skipped=true
        if (! $skipped) {
            validator(
                ['value' => $request->input('value')],
                ['value' => $rules],
                // Optional: custom messages per type could go here
            )->validate();
        }

        // Persist in `responses` (one row per run+step)
        DB::transaction(function () use ($surveyRun, $step, $skipped, $request) {
            Response::updateOrCreate(
                [
                    'survey_run_id' => $surveyRun->id,
                    'step_id' => $step->id,
                ],
                [
                    'survey_run_id' => $surveyRun->id,
                    'step_id' => $step->id,
                    'type' => $step->question_type,
                    'value' => $request->input('value'),
                    'is_skipped' => $skipped,
                ]
            );
        });

        return $this->redirectToNextStep(
            $practice,
            $patient,
            $surveyRun,
            $step
        );
    }

    public function summary(
        Practice $practice,
        Patient $patient,
        SurveyRun $surveyRun)
    {
        return inertia('patients/survey-runs/steps/Summary', [
            'practice' => $practice,
            'patient' => PatientResource::make($patient),
            'surveyRun' => SurveyRunResource::make($surveyRun->load('responses')),
            'survey' => SurveyResource::make($surveyRun->survey),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function redirectToNextStep(
        Practice $practice,
        Patient $patient,
        SurveyRun $surveyRun,
        Step $step
    ) {
        return $step->nextStep()
            ? redirect()->route('practices.patients.survey-runs.steps.show', [
                'practice' => $practice,
                'patient' => $patient,
                'survey_run' => $surveyRun,
                'step' => $step->nextStep() ?? $step,
            ])
            : redirect()->route('practices.patients.survey-runs.summary', [
                'practice' => $practice,
                'patient' => $patient,
                'survey_run' => $surveyRun,
            ]);
    }
}
