<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Practice;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Response;
use App\Models\Step;
use App\Models\Survey;
use App\Models\SurveyRun;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @throws \Throwable
     */
    public function run(): void
    {

        // Get first practice
        $practice = Practice::first();
        throw_if(! $practice, 'No practice found - register a user first');

        // Create 10 patients for the practice
        Patient::factory(10)
            ->create([
                'practice_hash' => $practice->getHash(),
            ]);

        // Create some surveys
        $surveys = Survey::factory(10)
            ->has(Step::factory(5), 'steps')
            ->create();

        // Create choices for those steps, that are type 'question' and
        // question_type 'multiple_choice' or 'single_choice'
        foreach ($surveys as $survey) {
            foreach ($survey->steps as $step) {
                if ($step->step_type === 'question' && in_array($step->question_type, ['multiple_choice', 'single_choice'])) {
                    $step->choices()->saveMany(Step::factory(5)->make());
                }
            }
        }

        // Now create random 1...3 survey runs for each user
        foreach (Patient::all() as $patient) {
            $surveyRuns = SurveyRun::factory(rand(1, 3))
                ->create([
                    'patient_hash' => $patient->getHash(),
                    'survey_id' => $surveys->random()->id,
                ]);

            // Create random responses for each survey run
            foreach ($surveyRuns as $surveyRun) {
                Response::factory(rand(1, 5))
                    ->create([
                        'survey_run_id' => $surveyRun->id,
                        'step_id' => $surveyRun->survey->steps->random()->id,
                    ]);
            }
        }

        // Create 10 SurveyRuns for each patient
        foreach (Patient::all() as $patient) {
            SurveyRun::factory(10)
                ->create([
                    'patient_hash' => $patient->getHash(),
                    'survey_id' => Survey::first()->id,
                ]);

        }
    }
}
