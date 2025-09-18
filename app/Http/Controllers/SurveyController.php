<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyRequest;
use App\Http\Requests\UpdateSurveyRequest;
use App\Http\Resources\SurveyResource;
use App\Models\Choice;
use App\Models\Practice;
use App\Models\Step;
use App\Models\Survey;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Throwable;

class SurveyController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Practice $practice)
    {
        return inertia('surveys/Index', [
            'surveys' => SurveyResource::collection(
                Survey::where('practice_hash', $practice->getHash())->get()
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Practice $practice)
    {
        $this->authorize('create', [Survey::class, $practice]);

        return inertia('surveys/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws Throwable
     */
    public function store(Practice $practice, StoreSurveyRequest $request)
    {
        $this->authorize('create', [Survey::class, $practice]);

        DB::transaction(function () use ($practice, $request) {

            $survey = $request->validated('survey');

            $persistedSurvey = Survey::create([
                'practice_hash' => $practice->getHash(),
                'name' => $survey['name'],
                'description' => $survey['description'],
                'version' => $survey['version'],
                'is_active' => $survey['is_active'],
            ]);

            foreach ($survey['steps'] as $step) {

                $persistedStep = Step::create([
                    'survey_id' => $persistedSurvey->id,
                    'order' => $step['order'],
                    'step_type' => $step['step_type'],
                    'question_type' => $step['question_type'] ?? null,
                    'options' => $step['options'] ?? null,
                    'title' => $step['title'],
                    'content' => $step['content'],
                ]);

                if (!empty($step['choices'])) {
                    foreach ($step['choices'] as $choice) {
                        Choice::create([
                            'step_id' => $persistedStep->id,
                            'order' => $choice['order'],
                            'label' => $choice['label'],
                            'value' => $choice['value'],
                            'optional_next_step' => $choice['next_step'] ?? null,
                        ]);
                    }
                }
            }
        });

        return inertia('surveys/Create');
    }

    /**
     * Display the specified resource.
     */
    public function show(Survey $survey)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Survey $survey)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyRequest $request, Survey $survey)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Survey $survey)
    {
        //
    }
}
