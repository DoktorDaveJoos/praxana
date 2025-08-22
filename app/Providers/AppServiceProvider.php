<?php

namespace App\Providers;

use App\Models\Patient;
use App\Models\Practice;
use App\Models\SurveyRun;
use App\Observers\SurveyRunObserver;
use App\Policies\PatientPolicy;
use App\Policies\PracticePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the policies
        Gate::policy(Practice::class, PracticePolicy::class);
        Gate::policy(Patient::class, PatientPolicy::class);

        // Register model observers
        SurveyRun::observe(SurveyRunObserver::class);
    }
}
