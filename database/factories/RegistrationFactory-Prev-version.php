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

    // Define course progression in sequence
    protected $courseSequence = [
        'ATB K-BT',  // Kindergarten/Basic Tools Module
        'ATB 1',     // Introductory Basic Module
        'ATB 2',     // Basic Module 2
        'ATB 3',     // Basic Module 3
        'ATB 4',     // Advanced Module 1
        'ATB 5',     // Advanced Module 2
        'M_Body',    // Embodiment Module
    ];

    public function definition(): array
    {
        // Select a random student
        $student = Student::inRandomOrder()->first();
        // dd($student);
        echo "Student ID: " . $student->id . "\n\n";
        echo "Student name: " . $student->first_name . " " . $student->last_name . "\n\n";


        // Get the student's last completed registration
        $lastCompletedRegistration = Registration::where('student_id', $student->id)
            ->where('end_status', 'completed')
            ->orderByDesc('event_id')
            ->with('event.course')
            ->first();

        echo "\$lastCompletedRegistration: " . $lastCompletedRegistration . "\n\n";



        // dd statement to check the last completed registration
        // dd('Last Completed Registration', $lastCompletedRegistration); // = null;

        // Determine the next course in the progression
        $lastCompletedCourseTitle = $lastCompletedRegistration
            ? $lastCompletedRegistration->event->course->course_code
            : null;

        echo "\$lastCompletedCourseTitle: " . $lastCompletedCourseTitle . "\n\n";

        // dd statement to check last completed course title
        // dd('Last Completed Course Title', $lastCompletedCourseTitle);

        // Get the index of the last completed course in the sequence
        $currentCourseIndex = $lastCompletedCourseTitle
            ? array_search($lastCompletedCourseTitle, $this->courseSequence)
            : 0; // If no course completed, start with the first course

        echo "\$currentCourseIndex: " . $currentCourseIndex . "\n\n";


        // dd statement to check the current course index
        // dd('Current Course Index', $currentCourseIndex);

        // Get the next course in the sequence
        $nextCourseCode = $this->courseSequence[$currentCourseIndex + 1] ?? null;


        echo "\$nextCourseCode: " . $nextCourseCode . "\n\n";

        // dd statement to check the next course code
        // dd('Next Course Code', $nextCourseCode);

        // dd($nextCourseCode ? 'true' : 'false');

        // If no further courses are available, skip registration
        if (!$nextCourseCode) {
            return []; // No more courses for the student to take
        }
        // Find the next course in the database
        $nextCourse = Course::where('course_code', $nextCourseCode)->first();

        echo "\$nextCourse: " . $nextCourse . "\n\n";

        // dd statement to check the next course
        // dd('Next Course', $nextCourse);

        // Find the next available event for this course
        $registeredEventIds = Registration::where('student_id', $student->id)
            ->pluck('event_id')
            ->toArray();

        echo "\$registeredEventIds: " . json_encode($registeredEventIds, JSON_PRETTY_PRINT) . "\n\n";

        // dd statement to check registered event ids

        // Get the date of the last completed event (if any)
        $lastCompletedDate = $lastCompletedRegistration
            ? $lastCompletedRegistration->event->dateto  // Use 'dateto' from the last completed event
            : null;  // No previous registration
        echo "\$lastCompletedDate: " . $lastCompletedDate . "\n\n";

        // dd('Next available', $registeredEventIds);

        // Find the next available event for the next course in sequence
        $events = Event::where('course_id', $nextCourse->id)
            ->whereNotIn('id', $registeredEventIds) // Exclude already registered events
            ->when($lastCompletedDate, function ($query) use ($lastCompletedDate) {
                // Ensure the event starts after the last completed event's 'dateto'
                return $query->where('dateto', '>', $lastCompletedDate);
            })
            ->orderBy('datefrom', 'asc') // Sort by the soonest available event
            ->get();

        // Check available events
        //  dd('Available events:', $events);

        // echo "\$events: " . json_encode($events, JSON_PRETTY_PRINT) . "\n\n";

        // dd statement to check available events
        // dd('Available Events', $events);

        // Find the first event that isn't already filled up
        $nextEvent = $events->first(function ($event) {
            return $event->participant_count < 10; // Limit participants per event
        });

        echo "\$nextEvent: " . $nextEvent . "\n\n";

        // dd statement to check the next selected event
        // dd('Next Event', $nextEvent);

        // If no event is available, return no registration
        if (!$nextEvent) {
            return [];
        }

        // Update the participant count for the selected event
        $nextEvent->increment('participant_count');

        // dd($student);

        return [
            'student_id' => $student->id,
            'event_id' => $nextEvent->id,
            'end_status' => $this->faker->randomElement(['completed', 'incomplete']),
            'comments' => $this->faker->optional()->sentence(),
        ];
    }
}
