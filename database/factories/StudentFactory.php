<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $gender = $this->faker->randomElement(['M', 'F']);

        return [
            'first_name' => $this->faker->firstName($gender === 'F' ? 'female' : 'male'),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'post_code' => $this->faker->postcode(),
            'dob' => $this->faker->date(),
            'gender' => $gender,
            'website' => $this->faker->url()
        ];
    }
}
