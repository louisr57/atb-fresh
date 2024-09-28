<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'gender' => $this->faker->randomElement(['Male', 'Female', 'Other']),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->unique()->phoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'post_code' => $this->faker->postcode(),
            'website' => $this->faker->url(),
            'dob' => $this->faker->dateTimeBetween($startDate = '-70 years', $endDate = '-20 years', $timezone = null),
            'ident' => $this->faker->uuid(), // Assuming 'ident' is a unique identifier
            'next_of_kin' => $this->faker->name(),
            'allergies' => $this->faker->word(), // Random word for simplicity
            'special_needs' => $this->faker->text(500), // Random text for special needs
        ];
    }
}
