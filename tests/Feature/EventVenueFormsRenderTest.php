<?php

use App\Models\Course;
use App\Models\Event;
use App\Models\Facilitator;
use App\Models\User;
use App\Models\Venue;

test('event and venue forms render, including after a clash and a similar-venue warning', function () {
    $this->actingAs(User::factory()->create());
    cache()->put('countries', [['name' => 'India']]);
    $course = Course::create(['course_code' => 'ATB 1', 'course_title' => "ATB 1 O'Neil", 'description' => 'x', 'prerequisites' => 'x', 'duration' => 3]);
    $venue = Venue::factory()->create(['venue_name' => 'Auromira']);
    $rosa = Facilitator::create(['first_name' => 'Rosa', 'last_name' => 'T', 'email' => 'r@x.test', 'address' => 'x', 'city' => 'x', 'country' => 'x']);
    $event = Event::create(['title' => 'ATB 1', 'course_id' => $course->id, 'venue_id' => $venue->id, 'datefrom' => '2026-09-05', 'dateto' => '2026-09-07', 'timefrom' => '08:30', 'timeto' => '17:00', 'participant_count' => 0]);
    $event->facilitators()->attach($rosa->id);

    $this->get(route('events.create'))->assertOk();
    $this->get(route('events.edit', $event))->assertOk()->assertSee('Rosa T');

    $payload = ['title' => 'ATB 1', 'course_id' => $course->id, 'venue_id' => $venue->id, 'facilitator_ids' => [$rosa->id], 'datefrom' => '2026-09-06', 'dateto' => '2026-09-06', 'timefrom' => '09:00', 'timeto' => '12:00'];
    $this->from(route('events.create'))->followingRedirects()->post(route('events.store'), $payload)
        ->assertOk()->assertSee('is already facilitating', false)->assertSee('2026-09-06');

    $this->get(route('venues.create'))->assertOk()->assertSee('Online / virtual venue');
    $this->get(route('venues.edit', $venue))->assertOk();
    $this->from(route('venues.create'))->followingRedirects()->post(route('venues.store'), [
        'venue_name' => 'Auromira', 'address' => 'a', 'city' => 'b', 'state' => 'c', 'country' => 'India', 'postcode' => '1',
        'vcontact_person' => 'p', 'vcontact_phone' => '1', 'vcontact_email' => 'e@x.test',
    ])->assertOk()->assertSee('This venue looks similar')->assertSee('confirm_not_duplicate', false);
    $this->get(route('venues.show', $venue))->assertOk();
});
