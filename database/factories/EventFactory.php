<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Course;
use App\Models\Facilitator;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EventFactory extends Factory
{
    protected $model = Event::class;

    // Cache for IDs
    protected static $courseIds = null;
    protected static $facilitatorIds = null;
    protected static $venueIds = null;

    protected function initializeIds()
    {
        if (self::$courseIds === null) {
            self::$courseIds = Course::pluck('id')->toArray();
        }
        if (self::$facilitatorIds === null) {
            self::$facilitatorIds = Facilitator::pluck('id')->toArray();
        }
        if (self::$venueIds === null) {
            self::$venueIds = Venue::pluck('id')->toArray();
        }
    }

    public function definition(): array
    {
        $this->initializeIds();

        // Get random IDs from cached arrays
        $courseId = $this->faker->randomElement(self::$courseIds);
        $course = Course::find($courseId);

        // Generate a random start date (datefrom)
        $datefrom = $this->faker->dateTimeBetween('-2 years', '+2 years');

        // Calculate dateto by adding the course's duration in days
        $durationInDays = (int) filter_var($course->duration, FILTER_SANITIZE_NUMBER_INT);
        $dateto = Carbon::instance($datefrom)->addDays($durationInDays);

        return [
            'title' => $course->course_title,
            'datefrom' => $datefrom->format('Y-m-d'),
            'dateto' => $dateto->format('Y-m-d'),
            'timefrom' => $this->faker->time(),
            'timeto' => $this->faker->time(),
            'venue_id' => $this->faker->randomElement(self::$venueIds),
            'course_id' => $courseId,
            'remarks' => $this->faker->optional()->sentence(),
            'participant_count' => 0,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Event $event) {
            // Attach 1-2 random facilitators to each event
            $numFacilitators = rand(1, 2);
            $facilitatorIds = $this->faker->randomElements(self::$facilitatorIds, $numFacilitators);
            $event->facilitators()->attach($facilitatorIds);
        });
    }
}
