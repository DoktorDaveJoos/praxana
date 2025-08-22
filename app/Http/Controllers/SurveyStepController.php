<?php

namespace App\Http\Controllers;

use App\Http\Resources\PatientResource;
use App\Http\Resources\StepResource;
use App\Http\Resources\SurveyResource;
use App\Http\Resources\SurveyRunResource;
use App\Models\Patient;
use App\Models\Practice;
use App\Models\Step;
use App\Models\SurveyRun;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

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
            'step' => StepResource::make($step),
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
     */
    public function update(Practice $practice, Patient $patient, SurveyRun $surveyRun, Step $step, Request $request)
    {
        $this->authorize('update', [SurveyRun::class, $practice, $patient, $surveyRun]);

        return redirect()->route('practices.patients.survey-runs.steps.show', [
            'practice' => $practice,
            'patient' => $patient,
            'survey_run' => $surveyRun,
            'step' => $step,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
