<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        // Fetch a random course from the courses table
        $course = Course::inRandomOrder()->first();

        // Generate a random start date (datefrom)
        $datefrom = $this->faker->dateTimeBetween('-1 years', '+1 years');

        // Calculate dateto by adding the course's duration in days to the datefrom value
        // Assuming the course's duration is stored as an integer or a string like "3 days", we extract the number of days
        $durationInDays = (int) filter_var($course->duration, FILTER_SANITIZE_NUMBER_INT);

        // Calculate the dateto value by adding the course duration to the datefrom
        $dateto = Carbon::instance($datefrom)->addDays($durationInDays);

        return [
            'title' => $course->course_title,
            'datefrom' => $datefrom->format('Y-m-d'),
            'dateto' => $dateto->format('Y-m-d'),
            'timefrom' => $this->faker->time(),
            'timeto' => $this->faker->time(),
            'venue' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'postcode' => $this->faker->postcode(),
            'location_geocode' => $this->faker->latitude() . ', ' . $this->faker->longitude(),
            'course_id' => $course->id,
            'instructor_id' => Instructor::inRandomOrder()->first()->id,
            'remarks' => $this->faker->optional()->sentence(),
            'participant_count' => 0,
        ];
    }
}
