<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Venue;

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
            'location_geocode' => $this->faker->latitude() . ', ' . $this->faker->longitude(),
            'remarks' => $this->faker->optional()->sentence(),
        ];
    }
}
