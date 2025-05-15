<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Practice;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Survey;
use App\Models\SurveyRun;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

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
        Patient::factory(50)
            ->create([
                'practice_hash' => $practice->getHash(),
            ]);

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
