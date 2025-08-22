<?php

namespace App\Observers;

use App\Jobs\ProcessSurveyCompletion;
use App\Models\SurveyRun;
use App\SurveyRunStatus;
use Illuminate\Support\Facades\Log;

class SurveyRunObserver
{
    /**
     * Handle the SurveyRun "created" event.
     */
    public function created(SurveyRun $surveyRun): void
    {
        //
    }

    /**
     * Handle the SurveyRun "updated" event.
     */
    public function updated(SurveyRun $surveyRun): void
    {
        // Check if the status has changed from pending to completed
        if ($surveyRun->isDirty('status')) {
            $originalStatus = $surveyRun->getOriginal('status');
            $newStatus = $surveyRun->status;

            Log::info("SurveyRun status change detected", [
                'survey_run_id' => $surveyRun->id,
                'original_status' => $originalStatus?->value ?? 'null',
                'new_status' => $newStatus?->value ?? 'null'
            ]);

            // Trigger job when status changes from pending to completed
            if ($originalStatus === SurveyRunStatus::Pending && $newStatus === SurveyRunStatus::Completed) {
                Log::info("Dispatching ProcessSurveyCompletion job for SurveyRun ID: {$surveyRun->id}");

                ProcessSurveyCompletion::dispatch($surveyRun);
            }
        }
    }

    /**
     * Handle the SurveyRun "deleted" event.
     */
    public function deleted(SurveyRun $surveyRun): void
    {
        //
    }

    /**
     * Handle the SurveyRun "restored" event.
     */
    public function restored(SurveyRun $surveyRun): void
    {
        //
    }

    /**
     * Handle the SurveyRun "force deleted" event.
     */
    public function forceDeleted(SurveyRun $surveyRun): void
    {
        //
    }
}
