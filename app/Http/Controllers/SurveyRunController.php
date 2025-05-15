<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyRunRequest;
use App\Http\Requests\UpdateSurveyRunRequest;
use App\Http\Resources\PatientResource;
use App\Http\Resources\SurveyRunResource;
use App\Models\Patient;
use App\Models\Practice;
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
    public function store(StoreSurveyRunRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Practice $practice, Patient $patient, SurveyRun $surveyRun)
    {
        $this->authorize('view', [SurveyRun::class, $practice, $patient, $surveyRun]);

        return inertia('patients/survey-runs/Show', [
            'patient' => PatientResource::make($patient),
            'surveyRun' => SurveyRunResource::make($surveyRun),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SurveyRun $surveyRun)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyRunRequest $request, SurveyRun $surveyRun)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SurveyRun $surveyRun)
    {
        //
    }
}
