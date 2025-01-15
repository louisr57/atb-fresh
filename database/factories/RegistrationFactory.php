<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Student;
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
        // echo "Student ID: " . $student->id . "\n\n";
        // echo "Student name: " . $student->first_name . " " . $student->last_name . "\n\n";

        // Get the list of course codes for courses the student has already completed
        $completedCourseCodes = Registration::where('student_id', $student->id)
            ->where('end_status', 'completed') // Filter only completed registrations
            ->with('event.course') // Eager load the course details via the event relationship
            ->get() // Get all completed registrations
            ->map(function ($registration) {
                return $registration->event->course->course_code; // Extract course_code from related course
            })
            // ->pluck('event.course.course_code') // Pluck the course_code from the related course model
            ->flatten() // Flatten the collection to a single level
            ->toArray(); // Convert the collection to an array
        // echo "\$completedCourseCodes: " . json_encode($completedCourseCodes, JSON_PRETTY_PRINT) . "\n\n";

        // Check if the student has completed all courses except the one with ID 0
        $allCoursesCompleted = count(array_diff($this->courseSequence, $completedCourseCodes)) === 1
            && in_array('ATB K-BT', $this->courseSequence); // both sides of the && must be true. If the student has completed all courses except for one (1) course (ATB K-BT), then all courses are completed.
        // echo "\$allCoursesCompleted: is ";
        // echo $allCoursesCompleted ? 'true' : 'false'; // This is a boolean value
        // echo "\n\n";

        // Get the full details of the student's last completed registration
        $lastCompletedRegistration = Registration::where('student_id', $student->id)
            ->join('events', 'registrations.event_id', '=', 'events.id') // Join the events table
            ->where('end_status', 'completed') // Note this is a second where clause
            ->orderByDesc('events.datefrom') // Get the latest completed event which will appear on top since the order is reversed (latest first in the list)
            ->get() // Get the collection of completed registrations
            ->first(); // Note that this returns a single model instance or (row), not a collection of rows.

        // print_r($lastCompletedRegistration);
        // var_dump($lastCompletedRegistration);
        // dd($lastCompletedRegistration);

        // echo "\$lastCompletedRegistration: " . $lastCompletedRegistration . " which is of type " . gettype($lastCompletedRegistration) . "\n\n";

        // Debug to check the result
        if (! $lastCompletedRegistration) {
            // echo "No completed registrations found.\n\n";
        } else {
            // Check if event and course are properly loaded
            if ($lastCompletedRegistration->event && $lastCompletedRegistration->event->course) {
                $lastCompletedCourseCode = $lastCompletedRegistration->event->course->course_code;
                // echo "\$lastCompletedCourseCode: " . $lastCompletedCourseCode . "\n\n";
            } else {
                // echo "Event or course information missing.\n\n";
            }
        }

        // Determine the next course in the progression
        $lastCompletedCourseCode = $lastCompletedRegistration
            ? $lastCompletedRegistration->event->course->course_code
            : null; // If no course completed, this will result in starting with the first course
        // echo "\$lastCompletedCourseCode: " . $lastCompletedCourseCode . " is of type " . gettype($lastCompletedCourseCode) . "\n\n";

        $currentCourseIndex = $lastCompletedCourseCode
            ? array_search($lastCompletedCourseCode, $this->courseSequence)
            : 0; // If no course completed, start with the first course
        // echo "\$currentCourseIndex: " . $currentCourseIndex . "\n\n"; // This is an array number, NOT a course code!

        if ($allCoursesCompleted) {
            // If all courses are completed, allow repeating any previous course except the one with ID 0
            $repeatableCourses = array_diff($completedCourseCodes, ['ATB K-BT']);
            $nextCourseCode = $this->faker->randomElement($repeatableCourses);
        } else {
            // Find the next course in sequence that hasn't been completed yet
            $nextCourseCode = null;
            for ($i = $currentCourseIndex + 1; $i < count($this->courseSequence); $i++) {
                if (! in_array($this->courseSequence[$i], $completedCourseCodes)) {
                    $nextCourseCode = $this->courseSequence[$i];
                    break;
                }
            }
        }
        // echo "\$nextCourseCode: " . $nextCourseCode . "\n\n";

        // If no further courses are available, return empty array (no registration)
        if (! $nextCourseCode) {
            return [];
        }

        // Find the next event with this course in the Event table
        $nextCourse = Course::where('course_code', $nextCourseCode)->first();
        // echo "\$nextCourse: " . $nextCourse . "\n\n";
        // Seems a bit early in the code for this check, but let's keep it here for now.

        if (! $nextCourse) {
            // echo "No course found for code: $nextCourseCode\n\n";
            return [];
        }

        $lastAttemptOnSameCourse = null; // Starting point for the variable value
        $lastAttemptOnSameCourse = Registration::where('student_id', $student->id)
            ->join('events', 'registrations.event_id', '=', 'events.id') // Join the events table
            ->where('events.course_id', $nextCourse->id)  // Filter by the course ID
            ->orderByDesc('events.datefrom')  // Order by the datefrom of the event
            ->select('registrations.*')  // Ensure we're selecting from the registrations table
            ->first();
        // echo "\$lastAttemptOnSameCourse: " . $lastAttemptOnSameCourse . "\n\n";

        // Get the baseline date as the most recent 'dateto' from all the student's registrations
        $baselineDate = Registration::where('student_id', $student->id)
            ->join('events', 'registrations.event_id', '=', 'events.id') // Join with events table
            ->orderByDesc('events.dateto') // Sort by 'dateto' in descending order
            ->value('events.dateto'); // Get the most recent 'dateto'

        // echo "\$baselineDate: " . ($baselineDate ?? 'No baseline date available') . "\n\n";

        // If no baseline date is found (i.e., student has no registrations), default to null or another strategy.
        if (! $baselineDate) {
            // echo "No previous registrations found. Defaulting to no baseline date.\n";
        }

        // Step 4: Find the next available event for this course after the baseline date
        $registeredEventIds = null; // Starting point for the variable value
        $registeredEventIds = Registration::where('student_id', $student->id)
            ->limit(5)
            ->pluck('event_id')
            ->toArray();
        // echo "\$registeredEventIds: " . json_encode($registeredEventIds, JSON_PRETTY_PRINT) . "\n\n";

        // Find the next available event for the course
        $events = Event::where('course_id', $nextCourse->id)
            ->whereNotIn('id', $registeredEventIds) // Exclude already registered events
            ->when($baselineDate, function ($query) use ($baselineDate) {
                return $query->where('datefrom', '>', $baselineDate);
            })
            ->orderBy('datefrom', 'asc') // Sort by soonest event date
            ->select('id', 'title', 'datefrom', 'dateto') // Select only specific fields
            ->limit(5) // Limit the number of events to 5
            ->get();

        // echo "\$events: " . json_encode($events, JSON_PRETTY_PRINT) . "\n\n";

        // echo "Available Events for the same course:\n";
        // foreach ($events as $event) {
        //     //    echo "Event ID: {$event->id}, Date: {$event->datefrom}, Participants: {$event->participant_count}\n";
        // }

        // Pick the first available event
        $nextEvent = $events->first(function ($event) {
            return $event->participant_count < 30; // Ensure it has available space and assigns to the $nextEvent variable, the first event that meets the condition
        });
        // echo "\$nextEvent: " . json_encode($nextEvent, JSON_PRETTY_PRINT) . "\n\n";

        // If no event is available, return no registration
        if (! $nextEvent) {
            // echo "No available events found for the course '{$nextCourse->course_title}' after {$baselineDate}\n\n";
            return []; // Exit early if no event is available
        }

        // Increment participant count for the selected event
        $nextEvent->increment('participant_count');
        $student->increment('reg_count');
        // echo "Next event selected: Event ID {$nextEvent->id}, {$nextEvent->title} starting on {$nextEvent->datefrom}\n";
        // echo "=========================================================================\n\n";

        // Return the registration details for the next event
        return [
            'student_id' => $student->id,
            'event_id' => $nextEvent->id, // Ensure this is valid
            'end_status' => $this->faker->randomElement(['registered','completed', 'incomplete']),
            'comments' => $this->faker->optional()->sentence(),
        ];
    }
}
