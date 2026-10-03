<?php

namespace App\Observers;

use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class RegistrationObserver
{
    public function created(Registration $registration)
    {
        // Update participant count using a direct query for better performance
        DB::table('events')
            ->where('id', $registration->event_id)
            ->increment('participant_count');
    }

    public function deleted(Registration $registration)
    {
        // Update participant count using a direct query for better performance
        // Note: with SoftDeletes this fires on soft delete too, which is correct
        // since a soft-deleted registration should no longer count as active.
        DB::table('events')
            ->where('id', $registration->event_id)
            ->decrement('participant_count');
    }

    public function restored(Registration $registration)
    {
        // Mirror image of deleted(): a restored registration becomes active again.
        DB::table('events')
            ->where('id', $registration->event_id)
            ->increment('participant_count');
    }
}
