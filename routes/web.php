<?php

use App\Http\Controllers\PracticeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;

Route::get('/', fn () => Inertia::render('Welcome'));

Route::middleware([
    'auth',
    ValidateSessionWithWorkOS::class,
])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Practices
    Route::controller(PracticeController::class)->group(function () {
        Route::get('/practices/{practice}', 'show')
            ->name('practices.show');
        Route::put('/practices', 'update')
            ->name('practices.update');
    });

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
