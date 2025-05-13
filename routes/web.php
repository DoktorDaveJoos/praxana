<?php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\PracticeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;

Route::get('/', fn () => Inertia::render('Welcome'));

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

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
