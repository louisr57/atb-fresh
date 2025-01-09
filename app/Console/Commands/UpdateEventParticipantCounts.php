<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class UpdateEventParticipantCounts extends Command
{
    protected $signature = 'events:update-counts';

    protected $description = 'Update participant counts for all events';

    public function handle()
    {
        $events = Event::all();
        $count = 0;

        foreach ($events as $event) {
            $event->updateParticipantCount();
            $count++;
        }

        $this->info("Updated participant counts for {$count} events.");
    }
}
