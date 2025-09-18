<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyRunRequest;
use App\Http\Requests\UpdateSurveyRunRequest;
use App\Http\Resources\PatientResource;
use App\Http\Resources\SurveyResource;
use App\Http\Resources\SurveyRunResource;
use App\Models\Patient;
use App\Models\Practice;
use App\Models\Survey;
use App\Models\SurveyRun;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SurveyRunController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Practice $practice, Patient $patient)
    {
        $this->authorize('viewAny', [SurveyRun::class, $practice, $patient]);

        return inertia('patients/survey-runs/Index', [
            'patient' => PatientResource::make($patient),
            'surveyRuns' => SurveyRunResource::collection(
                SurveyRun::where('patient_hash', $patient->getHash())->get()
            ),
            'surveys' => SurveyResource::collection(Survey::all()),
        ]);
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
    public function store(StoreSurveyRunRequest $request, Practice $practice, Patient $patient)
    {
        $this->authorize('create', [SurveyRun::class, $practice, $patient]);


        collect($request->validated('surveys'))
            ->each(function ($surveyId) use ($patient) {
                SurveyRun::create([
                    'patient_hash' => $patient->getHash(),
                    'survey_id' => $surveyId,
                    'current_step_id' => Survey::find($surveyId)->steps()->orderBy('order')->first()->id,
                ]);
            });

        return to_route('practices.patients.survey-runs.index', [
            'practice' => $practice,
            'patient' => $patient,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Practice $practice, Patient $patient, SurveyRun $surveyRun)
    {
        $this->authorize('view', [SurveyRun::class, $practice, $patient, $surveyRun]);

        return inertia('patients/survey-runs/Show', [
            'patient' => PatientResource::make($patient),
            'surveyRun' => SurveyRunResource::make($surveyRun->load('responses')),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Practice $practice, Patient $patient, SurveyRun $surveyRun)
    {
        $this->authorize('update', [SurveyRun::class, $practice, $patient, $surveyRun]);

        return inertia('patients/survey-runs/Edit', [
            'patient' => PatientResource::make($patient),
            'surveyRun' => SurveyRunResource::make($surveyRun),
            'survey' => SurveyResource::make(
                $surveyRun->survey->load(
                    'steps',
                    'steps.choices',
                )
            ),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Practice $practice, Patient $patient, SurveyRun $surveyRun, UpdateSurveyRunRequest $request)
    {
        $this->authorize('update', [SurveyRun::class, $practice, $patient, $surveyRun]);

        $validated = $request->validated();

        // Update the survey run with validated data
        $surveyRun->update($validated);

        return redirect()->route('practices.patients.survey-runs.show', [
            'practice' => $practice,
            'patient' => $patient,
            'survey_run' => $surveyRun
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SurveyRun $surveyRun)
    {
        //
    }
}
