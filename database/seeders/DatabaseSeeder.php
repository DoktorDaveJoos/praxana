<?php

namespace Database\Seeders;

use App\Models\Choice;
use App\Models\Patient;
use App\Models\Practice;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Response;
use App\Models\Step;
use App\Models\Survey;
use App\Models\SurveyRun;
use App\QuestionType;
use App\StepType;
use Illuminate\Database\Seeder;
use Throwable;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @throws Throwable
     */
    public function run(): void
    {

        // Get first practice
        $practice = Practice::first();
        throw_if(! $practice, 'No practice found - register a user first');

        // Create 10 patients for the practice
        $patients = Patient::factory(10)
            ->create([
                'practice_hash' => $practice->getHash(),
            ]);

        // Create some surveys
        $surveys = Survey::factory(10)
            ->has(Step::factory(rand(5, 10)), 'steps')
            ->create();

        // Create choices for those steps that are type 'question' and
        // question_type 'multiple_choice' or 'single_choice'
        foreach ($surveys as $survey) {
            foreach ($survey->steps as $step) {
                if (
                    $step->step_type === StepType::Question &&
                    in_array($step->question_type, [QuestionType::SingleChoice, QuestionType::MultipleChoice])
                ) {
                    if ($step->question_type === QuestionType::SingleChoice) {
                        $step->choices()->saveMany(Choice::factory(2)->make());
                    } else {
                        $step->choices()->saveMany(Choice::factory(rand(3, 5))->make());
                    }
                }
            }
        }

        // Now create random 1...3 survey runs for each user
        foreach ($patients as $patient) {

            $surveys->random(rand(1, 3))->each(function ($survey) use ($patient) {
                $surveyRun = SurveyRun::factory()
                    ->create([
                        'patient_hash' => $patient->getHash(),
                        'survey_id' => $survey->id,
                    ]);

                // Create some responses for this survey run
                $survey->steps->each(function ($step) use ($surveyRun) {
                    Response::factory()
                        ->create([
                            'survey_run_id' => $surveyRun->id,
                            'step_id' => $step->id,
                        ]);
                });
            });
        }
    }
}
