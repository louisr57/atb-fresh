<?php

use App\Models\Course;
use App\Models\Event;
use App\Models\Facilitator;
use App\Models\User;
use App\Models\Venue;

function conflictCourse(string $code): Course
{
    return Course::create([
        'course_code' => $code,
        'course_title' => $code,
        'description' => 'Test',
        'prerequisites' => 'none',
        'duration' => 3,
    ]);
}

function conflictFacilitator(string $first): Facilitator
{
    return Facilitator::create([
        'first_name' => $first,
        'last_name' => 'Test',
        'email' => strtolower($first).'@example.test',
        'address' => 'x',
        'city' => 'x',
        'country' => 'x',
    ]);
}

/**
 * Create an existing event directly (bypassing the controller check).
 */
function existingEvent(Course $course, Venue $venue, array $facilitators, string $from, string $to, string $tFrom = '08:30', string $tTo = '17:00'): Event
{
    $event = Event::create([
        'title' => $course->course_title,
        'course_id' => $course->id,
        'venue_id' => $venue->id,
        'datefrom' => $from,
        'dateto' => $to,
        'timefrom' => $tFrom,
        'timeto' => $tTo,
        'participant_count' => 0,
    ]);
    $event->facilitators()->attach(collect($facilitators)->pluck('id'));

    return $event;
}

function eventPayload(Course $course, Venue $venue, array $facilitators, string $from, string $to, string $tFrom = '08:30', string $tTo = '17:00'): array
{
    return [
        'title' => $course->course_title,
        'course_id' => $course->id,
        'venue_id' => $venue->id,
        'facilitator_ids' => collect($facilitators)->pluck('id')->all(),
        'datefrom' => $from,
        'dateto' => $to,
        'timefrom' => $tFrom,
        'timeto' => $tTo,
    ];
}

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    $this->atb1 = conflictCourse('ATB 1');
    $this->atb2 = conflictCourse('ATB 2');
    $this->auromira = Venue::factory()->create(['venue_name' => 'Auromira']);
    $this->elsewhere = Venue::factory()->create(['venue_name' => 'Elsewhere']);
    $this->rosa = conflictFacilitator('Rosa');
    $this->amir = conflictFacilitator('Amir');
});

test('an exact duplicate event is rejected', function () {
    existingEvent($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-07');

    $this->post(route('events.store'), eventPayload($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-07'))
        ->assertSessionHasErrors('schedule');

    expect(Event::count())->toBe(1);
});

test('a facilitator cannot teach another course at another venue on an overlapping day', function () {
    existingEvent($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-07');

    $this->post(route('events.store'), eventPayload($this->atb2, $this->elsewhere, [$this->rosa], '2026-09-07', '2026-09-09'))
        ->assertSessionHasErrors('schedule');

    expect(session('errors')->get('schedule')[0])->toContain('Rosa Test is already facilitating');
});

test('a venue cannot host two events at the same time even with different facilitators', function () {
    existingEvent($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-07');

    $this->post(route('events.store'), eventPayload($this->atb2, $this->auromira, [$this->amir], '2026-09-06', '2026-09-06'))
        ->assertSessionHasErrors('schedule');

    expect(session('errors')->get('schedule')[0])->toContain('Auromira is already booked');
});

test('an event is rejected if any one of its facilitators is busy', function () {
    existingEvent($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-07');

    $this->post(route('events.store'), eventPayload($this->atb2, $this->elsewhere, [$this->amir, $this->rosa], '2026-09-05', '2026-09-05'))
        ->assertSessionHasErrors('schedule');
});

test('different facilitators at different venues on the same days are allowed', function () {
    existingEvent($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-07');

    $this->post(route('events.store'), eventPayload($this->atb1, $this->elsewhere, [$this->amir], '2026-09-05', '2026-09-07'))
        ->assertSessionHasNoErrors();

    expect(Event::count())->toBe(2);
});

test('back-to-back events that do not overlap in dates are allowed', function () {
    existingEvent($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-07');

    $this->post(route('events.store'), eventPayload($this->atb2, $this->auromira, [$this->rosa], '2026-09-08', '2026-09-10'))
        ->assertSessionHasNoErrors();
});

test('half-day sessions on the same day are allowed when the hours do not overlap', function () {
    existingEvent($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-05', '08:30', '12:00');

    $this->post(route('events.store'), eventPayload($this->atb2, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-05', '12:00', '17:00'))
        ->assertSessionHasNoErrors();
});

test('half-day sessions on the same day are rejected when the hours overlap', function () {
    existingEvent($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-05', '08:30', '12:30');

    $this->post(route('events.store'), eventPayload($this->atb2, $this->elsewhere, [$this->rosa], '2026-09-05', '2026-09-05', '12:00', '17:00'))
        ->assertSessionHasErrors('schedule');
});

test('a virtual venue can host simultaneous events', function () {
    $zoom = Venue::factory()->create(['venue_name' => 'Zoom', 'is_virtual' => true]);
    existingEvent($this->atb1, $zoom, [$this->rosa], '2026-09-05', '2026-09-07');

    $this->post(route('events.store'), eventPayload($this->atb2, $zoom, [$this->amir], '2026-09-05', '2026-09-07'))
        ->assertSessionHasNoErrors();
});

test('a facilitator cannot teach two simultaneous online events', function () {
    $zoom = Venue::factory()->create(['venue_name' => 'Zoom', 'is_virtual' => true]);
    existingEvent($this->atb1, $zoom, [$this->rosa], '2026-09-05', '2026-09-07');

    $this->post(route('events.store'), eventPayload($this->atb2, $zoom, [$this->rosa], '2026-09-06', '2026-09-06'))
        ->assertSessionHasErrors('schedule');
});

test('soft-deleted events do not cause clashes', function () {
    existingEvent($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-07')->delete();

    $this->post(route('events.store'), eventPayload($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-07'))
        ->assertSessionHasNoErrors();
});

test('editing an event does not clash with itself', function () {
    $event = existingEvent($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-07');

    $this->put(route('events.update', $event), eventPayload($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-08'))
        ->assertSessionHasNoErrors();

    expect($event->fresh()->dateto)->toBe('2026-09-08');
});

test('an edit that creates a clash is rejected and nothing is saved', function () {
    existingEvent($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-07');
    $other = existingEvent($this->atb2, $this->elsewhere, [$this->amir], '2026-09-05', '2026-09-07');

    $this->put(route('events.update', $other), eventPayload($this->atb2, $this->elsewhere, [$this->amir, $this->rosa], '2026-09-05', '2026-09-07'))
        ->assertSessionHasErrors('schedule');

    expect($other->fresh()->facilitators->pluck('id')->all())->toBe([$this->amir->id]);
});

test('the conflicts command reports existing clashes', function () {
    existingEvent($this->atb1, $this->auromira, [$this->rosa], '2026-09-05', '2026-09-07');
    existingEvent($this->atb2, $this->elsewhere, [$this->rosa], '2026-09-06', '2026-09-06');

    $this->artisan('events:check-conflicts')
        ->expectsOutputToContain('1 clashing pair(s) found.')
        ->assertSuccessful();
});

test('the conflicts command can also report similar venues', function () {
    Venue::factory()->create(['venue_name' => 'Auromira Hall']);

    $this->artisan('events:check-conflicts --venues')
        ->expectsOutputToContain('1 similar venue pair(s) found.')
        ->expectsOutputToContain('No scheduling clashes found.')
        ->assertSuccessful();
});

