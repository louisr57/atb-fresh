<?php

use App\Models\Course;
use App\Models\Event;
use App\Models\Facilitator;
use App\Models\User;
use App\Models\Venue;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    cache()->put('countries', [['name' => 'India']]);
});

test('an online venue can be created without address, city, state or postcode', function () {
    $this->post(route('venues.store'), [
        'venue_name' => 'Online (Zoom)',
        'is_virtual' => '1',
        'country' => 'India',
        'address' => '',
        'city' => '',
        'state' => '',
        'postcode' => '',
    ])->assertSessionHasNoErrors();

    $venue = Venue::firstWhere('venue_name', 'Online (Zoom)');

    expect($venue)->not->toBeNull()
        ->and($venue->is_virtual)->toBeTrue()
        ->and($venue->address)->toBe('')
        ->and($venue->postcode)->toBe('');
});

test('a physical venue still requires address, city, state and postcode', function () {
    $this->post(route('venues.store'), [
        'venue_name' => 'Somewhere Hall',
        'country' => 'India',
    ])->assertSessionHasErrors(['address', 'city', 'state', 'postcode']);

    expect(Venue::count())->toBe(0);
});

test('country is still required for an online venue', function () {
    $this->post(route('venues.store'), [
        'venue_name' => 'Online (Zoom)',
        'is_virtual' => '1',
    ])->assertSessionHasErrors(['country']);
});

test('an existing venue can be switched to online and its address cleared', function () {
    $venue = Venue::factory()->create(['venue_name' => 'Zoom Room']);

    $this->put(route('venues.update', $venue), [
        'venue_name' => 'Zoom Room',
        'is_virtual' => '1',
        'country' => 'India',
        'address' => '',
    ])->assertSessionHasNoErrors();

    expect($venue->fresh())
        ->is_virtual->toBeTrue()
        ->address->toBe('');
});

test('display_name marks online venues', function () {
    expect(Venue::factory()->make(['venue_name' => 'Zoom', 'is_virtual' => true])->display_name)->toBe('Zoom (online)')
        ->and(Venue::factory()->make(['venue_name' => 'Auromira', 'is_virtual' => false])->display_name)->toBe('Auromira');
});

test('online venues are labelled in the event form, the event page and the venue list', function () {
    $zoom = Venue::factory()->create(['venue_name' => 'Zoom Main', 'is_virtual' => true]);
    Venue::factory()->create(['venue_name' => "O'Hara Hall", 'is_virtual' => false]);
    $course = Course::create(['course_code' => 'ATB 1', 'course_title' => 'ATB 1', 'description' => 'x', 'prerequisites' => 'x', 'duration' => 3]);
    $rosa = Facilitator::create(['first_name' => 'Rosa', 'last_name' => 'T', 'email' => 'r@x.test', 'address' => 'x', 'city' => 'x', 'country' => 'x']);
    $event = Event::create(['title' => 'ATB 1 online', 'course_id' => $course->id, 'venue_id' => $zoom->id, 'datefrom' => '2026-09-05', 'dateto' => '2026-09-07', 'timefrom' => '08:30', 'timeto' => '17:00', 'participant_count' => 0]);
    $event->facilitators()->attach($rosa->id);

    $this->get(route('events.create'))->assertOk()
        ->assertSee('Zoom Main')
        ->assertSee('(online)')
        ->assertDontSee("selectedText = 'O'Hara Hall'", false); // apostrophes no longer break the picker

    $this->get(route('events.edit', $event))->assertOk()->assertSee('Zoom Main (online)', false);
    $this->get(route('events.show', $event))->assertOk()->assertSee('Zoom Main')->assertSee('(online)');
    $this->get(route('venues.index'))->assertOk()->assertSee('Zoom Main (online)');
    $this->get(route('venues.create'))->assertOk()->assertSee('optional for online');
    $this->get(route('venues.edit', $zoom))->assertOk()->assertSee('online: true', false);
});
