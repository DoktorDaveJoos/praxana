<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePracticeRequest;
use App\Http\Requests\UpdatePracticeRequest;
use App\Http\Resources\PracticeResource;
use App\Models\Practice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PracticeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // @todo: Implement when user can have multiple practices
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // @todo: Implement when user can have multiple practices
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePracticeRequest $request)
    {
        // @todo: Implement when user can have multiple practices
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Practice $practice): Response
    {
        $this->authorize('view', $practice);

        return Inertia::render('practices/Show', [
            'practice' => PracticeResource::make($practice),
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
    public function update(UpdatePracticeRequest $request, Practice $practice)
    {
        $this->authorize('update', $practice);

        $practice->update($request->validated());

        return to_route('practices.show', $practice);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // @todo: Implement when user can have multiple practices
    }
}
