<?php

namespace Database\Factories;

use App\Models\Registration;
use App\Models\Student;
use App\Models\Event;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        // Fetch or generate a student
        $student = Student::inRandomOrder()->first();

        // Get the highest possible course ID (assumed to be the max course ID in the table)
        $highestCourseLevel = Course::max('id');

        // Get the student's previous registrations, ordered by course level
        $lastRegistration = Registration::where('student_id', $student->id)
            ->orderByDesc('event_id')
            ->with('event.course')
            ->first();

        // Determine the next course the student should take
        if ($lastRegistration) {
            $lastCourseId = $lastRegistration->event->course->id;

            // If the student has completed the highest course, allow repeating any previously completed course
            if ($lastCourseId === $highestCourseLevel && $lastRegistration->end_status === 'completed') {
                // Get a random previously completed course
                $previousCompletedCourses = Registration::where('student_id', $student->id)
                    ->where('end_status', 'completed')
                    ->pluck('event.course_id')
                    ->toArray();

                // Select a random previously completed course
                $nextCourseId = $this->faker->randomElement($previousCompletedCourses);
            } else {
                // If the last course was incomplete or cancelled, redo the same course
                if (in_array($lastRegistration->end_status, ['incomplete', 'cancelled'])) {
                    $nextCourseId = $lastCourseId; // Repeat the course
                } else {
                    // Otherwise, move to the next course in sequence
                    $nextCourseId = $lastCourseId < $highestCourseLevel
                        ? $lastCourseId + 1
                        : $highestCourseLevel;
                }
            }
        } else {
            // If no previous registration, assign course_id = 1 (Level 1)
            $nextCourseId = 1;
        }

        // Find an event (event entry) for the next course that occurs after the last attended event (if any)
        $nextEvent = Event::where('course_id', $nextCourseId)
            ->when($lastRegistration, function ($query) use ($lastRegistration) {
                // Ensure that the next event starts after the previous course end date
                return $query->where('datefrom', '>', $lastRegistration->event->dateto);
            })
            ->inRandomOrder()
            ->first();

        return [
            'student_id' => $student->id,
            'event_id' => $nextEvent->id,
            'end_status' => $this->faker->randomElement(['completed', 'incomplete']),
            'comments' => $this->faker->optional()->sentence(),
        ];
    }
}
