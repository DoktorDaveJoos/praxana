<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\Practice;
use App\Models\User;

class PatientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Practice $practice): bool
    {
        return $user->practice_id === $practice->id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Practice $practice, Patient $patient): bool
    {
        return $user->practice_id === $practice->id
            && $patient->practice_hash === $practice->getHash();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Practice $practice): bool
    {
        // @todo: Add roles and permissions - hint: use work_os
        return $user->practice_id === $practice->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Practice $practice, Patient $patient): bool
    {
        return $user->practice_id === $practice->id
            && $patient->practice_hash === $practice->getHash();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Practice $practice, Patient $patient): bool
    {
        return $user->practice_id === $practice->id
            && $patient->practice_hash === $practice->getHash();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Patient $patient): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Patient $patient): bool
    {
        return false;
    }
}
