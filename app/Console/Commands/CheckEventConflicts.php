<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Venue;
use App\Services\EventConflictChecker;
use App\Services\VenueSimilarityChecker;
use Illuminate\Console\Command;

/**
 * Read-only report of existing scheduling clashes (facilitator or venue double-bookings).
 * Useful before/after deploying the clash rule, since editing a clashing event
 * will be blocked until the clash is resolved.
 */
class CheckEventConflicts extends Command
{
    protected $signature = 'events:check-conflicts {--venues : Also list venues that look like duplicates of each other}';

    protected $description = 'List existing events that double-book a facilitator or a (non-virtual) venue';

    public function handle(EventConflictChecker $checker, VenueSimilarityChecker $venueChecker): int
    {
        if ($this->option('venues')) {
            $this->reportSimilarVenues($venueChecker);
        }

        $events = Event::with('facilitators')->orderBy('datefrom')->get();
        $reported = [];
        $count = 0;

        foreach ($events as $event) {
            $conflicts = $checker->conflictingEvents(
                $event->only(['datefrom', 'dateto', 'timefrom', 'timeto', 'venue_id']),
                $event->facilitators->pluck('id')->all(),
                $event->id
            );

            foreach ($conflicts as $conflict) {
                $pair = collect([$event->id, $conflict['event']->id])->sort()->implode('-');
                if (isset($reported[$pair])) {
                    continue;
                }
                $reported[$pair] = true;
                $count++;

                $this->warn("Event #{$event->id} ({$event->title}, {$event->datefrom} to {$event->dateto}) clashes with:");
                foreach ($conflict['messages'] as $message) {
                    $this->line("  - {$message}");
                }
            }
        }

        if ($count === 0) {
            $this->info('No scheduling clashes found.');
        } else {
            $this->newLine();
            $this->info("{$count} clashing pair(s) found.");
        }

        return self::SUCCESS;
    }

    private function reportSimilarVenues(VenueSimilarityChecker $venueChecker): void
    {
        $seen = [];

        foreach (Venue::orderBy('id')->get() as $venue) {
            foreach ($venueChecker->similarTo($venue->only(['venue_name', 'address', 'city']), $venue->id) as $match) {
                $pair = collect([$venue->id, $match['id']])->sort()->implode('-');
                if (isset($seen[$pair])) {
                    continue;
                }
                $seen[$pair] = true;
                $this->warn("Venue #{$venue->id} \"{$venue->venue_name}\" looks like #{$match['id']} \"{$match['name']}\": ".implode('; ', $match['reasons']));
            }
        }

        $this->info(count($seen).' similar venue pair(s) found.');
        $this->newLine();
    }
}
