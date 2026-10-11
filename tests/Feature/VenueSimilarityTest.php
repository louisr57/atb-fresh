<?php

use App\Models\User;
use App\Models\Venue;

function venuePayload(array $overrides = []): array
{
    return array_merge([
        'venue_name' => 'New Place',
        'address' => '1 Road',
        'city' => 'Auroville',
        'state' => 'Tamil Nadu',
        'country' => 'India',
        'postcode' => '605101',
        'vcontact_person' => 'Someone',
        'vcontact_phone' => '123',
        'vcontact_email' => 'a@b.test',
    ], $overrides);
}

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Venue::factory()->create(['venue_name' => 'Auromira', 'address' => 'Main Road', 'city' => 'Auroville']);
});

test('a venue with a similar name triggers a warning and is not saved', function () {
    $this->from(route('venues.create'))
        ->post(route('venues.store'), venuePayload(['venue_name' => 'auromira!']))
        ->assertRedirect(route('venues.create'))
        ->assertSessionHas('similar_venues');

    expect(Venue::count())->toBe(1);
});

test('a slightly misspelled venue name triggers a warning', function () {
    $this->from(route('venues.create'))
        ->post(route('venues.store'), venuePayload(['venue_name' => 'Auromirra']))
        ->assertSessionHas('similar_venues');
});

test('a venue whose name words are all contained in an existing name triggers a warning', function () {
    Venue::factory()->create(['venue_name' => 'Ashram GFU Raices', 'address' => 'Allende', 'city' => 'Allende']);

    $this->from(route('venues.create'))
        ->post(route('venues.store'), venuePayload(['venue_name' => 'Ashram Raíces', 'city' => 'Allende']))
        ->assertSessionHas('similar_venues');
});

test('sharing a single common word does not trigger a warning', function () {
    Venue::factory()->create(['venue_name' => 'Ashram el Paraiso', 'address' => 'El cerrito', 'city' => 'Cali']);

    $this->post(route('venues.store'), venuePayload(['venue_name' => 'Ashram Piedras del Sol', 'city' => 'Tanti']))
        ->assertSessionMissing('similar_venues');
});

test('a venue at the same address triggers a warning', function () {
    $this->from(route('venues.create'))
        ->post(route('venues.store'), venuePayload(['venue_name' => 'Green Hall', 'address' => 'Main Road']))
        ->assertSessionHas('similar_venues');
});

test('the warning can be acknowledged and the venue is then saved', function () {
    $this->post(route('venues.store'), venuePayload(['venue_name' => 'Auromira - Small Hall', 'confirm_not_duplicate' => '1']))
        ->assertSessionHasNoErrors()
        ->assertSessionMissing('similar_venues');

    expect(Venue::count())->toBe(2);
});

test('an unrelated venue is saved without a warning', function () {
    $this->post(route('venues.store'), venuePayload(['venue_name' => 'Villa de Leyva Centre', 'city' => 'Villa de Leyva']))
        ->assertSessionMissing('similar_venues');

    expect(Venue::count())->toBe(2);
});

test('editing a venue does not warn about itself and saves the virtual flag', function () {
    $venue = Venue::first();

    $this->put(route('venues.update', $venue), venuePayload(['venue_name' => 'Auromira', 'address' => 'Main Road', 'is_virtual' => '1']))
        ->assertSessionMissing('similar_venues');

    expect($venue->fresh()->is_virtual)->toBeTrue();
});
