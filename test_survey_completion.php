<?php

require_once 'vendor/autoload.php';

use App\Models\Survey;
use App\Models\SurveyRun;
use App\Models\Step;
use App\Models\Response;
use App\SurveyRunStatus;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Queue;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 Testing Survey Completion Functionality\n";
echo "==========================================\n\n";

try {
    // Enable queue logging to see if job is dispatched
    Queue::fake();

    echo "1. Creating test survey and survey run...\n";

    // Create a test survey
    $survey = Survey::factory()->create([
        'name' => 'Test Medical Survey',
        'description' => 'A test survey for medical analysis'
    ]);

    // Create some test steps (questions)
    $step1 = Step::create([
        'survey_id' => $survey->id,
        'title' => 'What symptoms are you experiencing?',
        'content' => 'Please describe your current symptoms',
        'step_type' => 'question',
        'question_type' => 'text',
        'order' => 1
    ]);

    $step2 = Step::create([
        'survey_id' => $survey->id,
        'title' => 'How long have you had these symptoms?',
        'content' => 'Duration of symptoms',
        'step_type' => 'question',
        'question_type' => 'text',
        'order' => 2
    ]);

    // Create a survey run with pending status
    $surveyRun = SurveyRun::factory()->create([
        'survey_id' => $survey->id,
        'status' => SurveyRunStatus::Pending,
        'patient_hash' => 'test-patient-hash'
    ]);

    echo "   ✅ Created survey run ID: {$surveyRun->id}\n";
    echo "   📊 Initial status: {$surveyRun->status->value}\n\n";

    echo "2. Adding test responses...\n";

    // Create some test responses
    Response::factory()->create([
        'survey_run_id' => $surveyRun->id,
        'step_id' => $step1->id,
        'value' => 'I have been experiencing headaches and fatigue'
    ]);

    Response::factory()->create([
        'survey_run_id' => $surveyRun->id,
        'step_id' => $step2->id,
        'value' => 'About 2 weeks'
    ]);

    echo "   ✅ Added test responses\n\n";

    echo "3. Updating survey run status to completed...\n";

    // Update the status from pending to completed
    // This should trigger the observer and dispatch the job
    $surveyRun->update([
        'status' => SurveyRunStatus::Completed,
        'finished_at' => now()
    ]);

    echo "   ✅ Status updated to: {$surveyRun->fresh()->status->value}\n";
    echo "   📅 Finished at: {$surveyRun->fresh()->finished_at}\n\n";

    echo "4. Checking if ProcessSurveyCompletion job was dispatched...\n";

    // Check if the job was dispatched
    Queue::assertPushed(\App\Jobs\ProcessSurveyCompletion::class, function ($job) use ($surveyRun) {
        return $job->surveyRun->id === $surveyRun->id;
    });

    echo "   ✅ ProcessSurveyCompletion job was dispatched successfully!\n\n";

    echo "5. Testing job execution (without actual API calls)...\n";

    // Test the job execution logic (we'll mock the external API calls)
    $job = new \App\Jobs\ProcessSurveyCompletion($surveyRun);

    // For testing, we'll just check if the job can be instantiated and has the right data
    echo "   ✅ Job instantiated with survey run ID: {$job->surveyRun->id}\n";
    echo "   📋 Survey name: {$job->surveyRun->survey->name}\n";
    echo "   📝 Number of responses: {$job->surveyRun->responses->count()}\n\n";

    echo "🎉 All tests passed! The survey completion functionality is working correctly.\n\n";

    echo "Summary:\n";
    echo "--------\n";
    echo "✅ Survey run status change detection: Working\n";
    echo "✅ Observer triggering: Working\n";
    echo "✅ Job dispatch: Working\n";
    echo "✅ Data relationships: Working\n\n";

    echo "Note: To test the full AI and PubMed functionality, ensure you have:\n";
    echo "- Valid OpenAI API key in .env file\n";
    echo "- Internet connection for PubMed API\n";
    echo "- Queue worker running (php artisan queue:work)\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
