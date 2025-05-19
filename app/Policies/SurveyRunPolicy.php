<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\Practice;
use App\Models\SurveyRun;
use App\Models\User;

class SurveyRunPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Practice $practice, Patient $patient): bool
    {
        return $practice->users->contains($user) &&
            $patient->practice_hash === $practice->getHash();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Practice $practice, Patient $patient, SurveyRun $surveyRun): bool
    {
        return $practice->users->contains($user) &&
            $patient->practice_hash === $practice->getHash() &&
            $surveyRun->patient_hash === $patient->getHash();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Practice $practice, Patient $patient): bool
    {
        return $practice->users->contains($user) &&
            $patient->practice_hash === $practice->getHash();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SurveyRun $surveyRun): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SurveyRun $surveyRun): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SurveyRun $surveyRun): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SurveyRun $surveyRun): bool
    {
        return false;
    }
}
