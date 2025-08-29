<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyRequest;
use App\Http\Requests\UpdateSurveyRequest;
use App\Http\Resources\SurveyResource;
use App\Models\Practice;
use App\Models\Survey;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
     */
    public function store(StoreSurveyRequest $request)
    {
        //
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
