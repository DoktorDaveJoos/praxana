<?php

namespace App\Observers;

use App\Models\Practice;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        // Create a practice for the user
        $practice = Practice::create([
            'name' => $user->name."'s Praxis",
        ]);

        $user->practice_id = $practice->id;
        $user->save();
    }
}
