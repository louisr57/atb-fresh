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
        DB::table('events')
            ->where('id', $registration->event_id)
            ->decrement('participant_count');
    }
}
