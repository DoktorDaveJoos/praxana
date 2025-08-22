<?php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\SurveyRunController;
use App\Http\Controllers\SurveyStepController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;

Route::get('/', fn() => Inertia::render('Welcome'));

Route::middleware([
    'auth',
    ValidateSessionWithWorkOS::class,
])->group(function () {

    // Dashboard
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Practices - (show and update only)
    Route::resource('practices', PracticeController::class)
        ->only(['show', 'update']);

    // Patients always under /practices/{practice}/patients
    Route::resource('practices.patients', PatientController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy']);

    // SurveysRuns always under /practices/{practice}/patients/{patient}/survey-runs
    Route::resource('practices.patients.survey-runs', SurveyRunController::class)
        ->only(['index', 'show', 'store', 'edit', 'update', 'destroy']);

    /**
     * Steps (fetch/save one step at a time)
     * /practices/{practice}/patients/{patient}/survey-runs/{survey_run}/steps/{step}
     */
    Route::resource('practices.patients.survey-runs.steps', SurveyStepController::class)
        ->only(['index', 'show', 'update']);
    // index (optional): get outline/metadata of all steps for a run
    // show: fetch a single step (question/dialog)
    // update: save answers/progress for that step

    // Convenience routes (optional but handy in clients)
//    Route::get(
//        'practices/{practice}/patients/{patient}/survey-runs/{survey_run}/steps/current',
//        [SurveyStepController::class, 'current']
//    )->name('practices.patients.survey-runs.steps.current');
//
//    Route::post(
//        'practices/{practice}/patients/{patient}/survey-runs/{survey_run}/steps/{step}/complete',
//        [SurveyStepController::class, 'complete']
//    )->name('practices.patients.survey-runs.steps.complete');
//
//    Route::post(
//        'practices/{practice}/patients/{patient}/survey-runs/{survey_run}/steps/{step}/next',
//        [SurveyStepController::class, 'next']
//    )->name('practices.patients.survey-runs.steps.next');

});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
