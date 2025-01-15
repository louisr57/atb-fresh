<?php

namespace Database\Factories;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

class VenueFactory extends Factory
{
    protected $model = Venue::class;

    public function definition(): array
    {
        return [
            'venue_name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'postcode' => $this->faker->postcode(),
            'location_geocode' => $this->faker->latitude().', '.$this->faker->longitude(),
            'vcontact_person' => $this->faker->name(),
            'vcontact_phone' => $this->faker->phoneNumber(),
            'vcontact_email' => $this->faker->email(),
            'remarks' => $this->faker->optional()->sentence(),
        ];
    }
}
