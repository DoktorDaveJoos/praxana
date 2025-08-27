<?php

namespace Database\Seeders;

use App\Models\Choice;
use App\Models\Patient;
use App\Models\Practice;
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
     * @throws Throwable
     */
    public function run(): void
    {
        $practice = Practice::first();
        throw_if(! $practice, 'No practice found - register a user first');

        $patients = Patient::factory(10)->create([
            'practice_hash' => $practice->getHash(),
        ]);

        // Create surveys
        $surveys = Survey::factory(10)->create();

        foreach ($surveys as $survey) {
            $stepsCount = rand(5, 10);

            // Create steps with order
            for ($i = 1; $i <= $stepsCount; $i++) {
                $step = Step::factory()->create([
                    'survey_id' => $survey->id,
                    'order'     => $i,
                ]);

                // Add choices if needed
                if (
                    $step->step_type === StepType::Question &&
                    in_array($step->question_type, [QuestionType::SingleChoice, QuestionType::MultipleChoice], true)
                ) {
                    $choiceCount = $step->question_type === QuestionType::SingleChoice ? 2 : rand(3, 5);
                    $step->choices()->saveMany(Choice::factory($choiceCount)->make());
                }
            }
        }

        // Create survey runs and responses
        foreach ($patients as $patient) {
            $surveys->random(rand(1, 3))->each(function (Survey $survey) use ($patient) {
                $surveyRun = SurveyRun::factory()->create([
                    'patient_hash' => $patient->getHash(),
                    'survey_id'    => $survey->id,
                ]);

                $survey->steps->each(function (Step $step) use ($surveyRun) {
                    // Only answer non-dialog steps that actually have a question type
                    if ($step->step_type !== StepType::Dialog && $step->question_type) {

                        // Ensure only ONE response per (survey_run_id, step_id)
                        $alreadyExists = Response::where('survey_run_id', $surveyRun->id)
                            ->where('step_id', $step->id)
                            ->exists();

                        if (! $alreadyExists) {
                            Response::factory()
                                ->forStep($step) // <- sets value + type based on the step's question_type
                                ->create([
                                    'survey_run_id' => $surveyRun->id,
                                    'step_id'       => $step->id,
                                ]);
                        }
                    }
                });
            });
        }
    }
}
