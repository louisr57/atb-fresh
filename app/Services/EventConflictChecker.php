<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Facilitator;
use App\Models\Venue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Detects scheduling clashes between events.
 *
 * Rule: two (non-deleted) events clash when their date ranges overlap AND their
 * daily time windows overlap, and at least one of the following is true:
 *  - they share a facilitator (a person cannot be in two places at once), or
 *  - they use the same venue and that venue is not virtual (one event per room).
 *
 * Time windows touching at the boundary (e.g. 08:30-12:00 and 12:00-17:00) do
 * not clash, which allows future half-day sessions to share a day.
 */
class EventConflictChecker
{
    /**
     * Return human-readable conflict messages for a proposed event.
     *
     * @param  array  $data  Must contain datefrom, dateto, timefrom, timeto, venue_id.
     * @param  array  $facilitatorIds  Facilitator ids proposed for the event.
     * @param  int|null  $ignoreEventId  The event being edited (excluded from the check).
     * @return array<int, string>
     */
    public function conflictsFor(array $data, array $facilitatorIds, ?int $ignoreEventId = null): array
    {
        $messages = [];

        foreach ($this->conflictingEvents($data, $facilitatorIds, $ignoreEventId) as $conflict) {
            $messages = array_merge($messages, $conflict['messages']);
        }

        return $messages;
    }

    /**
     * Return the conflicting events together with the reasons they clash.
     *
     * @return Collection<int, array{event: Event, messages: array<int, string>}>
     */
    public function conflictingEvents(array $data, array $facilitatorIds, ?int $ignoreEventId = null): Collection
    {
        $dateFrom = Carbon::parse($data['datefrom'])->toDateString();
        $dateTo = Carbon::parse($data['dateto'] ?? $data['datefrom'])->toDateString();
        $venueId = (int) $data['venue_id'];
        $facilitatorIds = array_values(array_unique(array_map('intval', $facilitatorIds)));

        $venue = Venue::find($venueId);
        $checkVenue = $venue && ! $venue->is_virtual;

        if (! $checkVenue && empty($facilitatorIds)) {
            return collect();
        }

        $candidates = Event::query()
            ->with(['course', 'venue', 'facilitators'])
            ->when($ignoreEventId, fn ($q) => $q->where('events.id', '!=', $ignoreEventId))
            // Date ranges overlap (a NULL dateto is treated as a single-day event)
            ->where('datefrom', '<=', $dateTo)
            ->whereRaw('COALESCE(dateto, datefrom) >= ?', [$dateFrom])
            ->where(function ($q) use ($checkVenue, $venueId, $facilitatorIds) {
                if ($checkVenue) {
                    $q->orWhere('venue_id', $venueId);
                }
                if (! empty($facilitatorIds)) {
                    $q->orWhereHas('facilitators', fn ($f) => $f->whereIn('facilitators.id', $facilitatorIds));
                }
            })
            ->orderBy('datefrom')
            ->get();

        $from = $this->minutes($data['timefrom']);
        $to = $this->minutes($data['timeto']);

        return $candidates
            ->filter(fn (Event $e) => $from < $this->minutes($e->timeto) && $this->minutes($e->timefrom) < $to)
            ->map(function (Event $e) use ($checkVenue, $venueId, $facilitatorIds) {
                $messages = [];
                $when = $this->describeWhen($e);
                $course = $e->course->course_title ?? $e->title;
                $venueName = $e->venue->venue_name ?? 'unknown venue';

                $e->facilitators
                    ->whereIn('id', $facilitatorIds)
                    ->each(function (Facilitator $f) use (&$messages, $course, $venueName, $when, $e) {
                        $messages[] = "{$f->first_name} {$f->last_name} is already facilitating \"{$course}\" at {$venueName} {$when} (event #{$e->id}).";
                    });

                if ($checkVenue && (int) $e->venue_id === $venueId) {
                    $messages[] = "{$venueName} is already booked for \"{$course}\" {$when} (event #{$e->id}). If the venue has more than one room, each room must be a separate venue.";
                }

                return ['event' => $e, 'messages' => $messages];
            })
            ->filter(fn ($c) => ! empty($c['messages']))
            ->values();
    }

    private function minutes(?string $time): int
    {
        if (! $time) {
            return 0;
        }

        $t = Carbon::parse($time);

        return $t->hour * 60 + $t->minute;
    }

    private function describeWhen(Event $e): string
    {
        $from = Carbon::parse($e->datefrom);
        $to = Carbon::parse($e->dateto ?? $e->datefrom);

        $dates = $from->isSameDay($to)
            ? 'on '.$from->format('j M Y')
            : 'from '.$from->format('j M').' to '.$to->format('j M Y');

        $times = Carbon::parse($e->timefrom)->format('H:i').'-'.Carbon::parse($e->timeto)->format('H:i');

        return "{$dates}, {$times}";
    }
}
