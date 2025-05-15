<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Models\Practice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PatientController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Practice $practice)
    {
        $this->authorize('viewAny', [Patient::class, $practice]);

        return inertia('patients/Index', [
            'patients' => PatientResource::collection(
                Patient::where('practice_hash', $practice->getHash())->get()
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
    public function store(StorePatientRequest $request, Practice $practice)
    {
        $this->authorize('create', [Patient::class, $practice]);

        $patient = Patient::create(
            $request->validated() +
            ['practice_hash' => $practice->getHash()]
        );

        return to_route('practices.patients.show', [$practice, $patient]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Practice $practice, Patient $patient)
    {
        $this->authorize('view', [Patient::class, $practice, $patient]);

        return inertia('patients/show/BasicData', [
            'patient' => PatientResource::make($patient),
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
    public function update(UpdatePatientRequest $request, Practice $practice, Patient $patient)
    {
        $this->authorize('update', [Patient::class, $practice, $patient]);

        $patient->update($request->validated());

        return to_route('practices.patients.show', [$practice, $patient]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Practice $practice, Patient $patient)
    {
        $this->authorize('delete', [Patient::class, $practice, $patient]);

        $patient->delete();

        return to_route('practices.patients.index', $practice);
    }
}
