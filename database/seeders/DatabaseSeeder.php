<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Practice;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        Patient::factory(50)->create([
            'practice_hash' => $practice->getHash(),
        ]);

    }
}
