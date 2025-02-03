<?php

namespace App\Observers;

use App\Models\Facilitator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FacilitatorObserver
{
    /**
     * Handle the Facilitator "creating" event.
     */
    public function creating(Facilitator $facilitator): void
    {
        // Check if a user with this email already exists
        $user = (new User)->newQuery()->where('email', $facilitator->email)->first();

        if (!$user) {
            // Create new user
            $user = (new User)->newQuery()->create([
                'name' => $facilitator->first_name . ' ' . $facilitator->last_name,
                'email' => $facilitator->email,
                'password' => Hash::make('password'),
            ]);
        }

        // Link the user to the facilitator
        $facilitator->user_id = $user->id;
    }

    /**
     * Handle the Facilitator "updated" event.
     */
    public function updated(Facilitator $facilitator): void
    {
        //
    }

    /**
     * Handle the Facilitator "deleted" event.
     */
    public function deleted(Facilitator $facilitator): void
    {
        //
    }

    /**
     * Handle the Facilitator "restored" event.
     */
    public function restored(Facilitator $facilitator): void
    {
        //
    }

    /**
     * Handle the Facilitator "force deleted" event.
     */
    public function forceDeleted(Facilitator $facilitator): void
    {
        //
    }
}
