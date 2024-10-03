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
        echo "Student ID: " . $student->id . "\n\n";
        echo "Student name: " . $student->first_name . " " . $student->last_name . "\n\n";


        // Get the list of course codes for courses the student has already completed
        $completedCourseCodes = Registration::where('student_id', $student->id)
            ->where('end_status', 'completed')
            ->with('event.course')
            ->get()
            ->pluck('event.course.course_code')
            ->toArray();
        echo "\$completedCourseCodes: " . json_encode($completedCourseCodes, JSON_PRETTY_PRINT) . "\n\n";

        // Check if the student has completed all courses except the one with ID 0
        $allCoursesCompleted = count(array_diff($this->courseSequence, $completedCourseCodes)) === 1
            && in_array('ATB K-BT', $completedCourseCodes);  // Replace 'ATB K-BT' with your actual course code for course ID 0

        // Step 1: Get the student's last completed registration for normal progression
        $lastCompletedRegistration = Registration::where('student_id', $student->id)
            ->where('end_status', 'completed')
            ->orderByDesc('event_id') // Get the latest completed event which will be at the top and picked by 'first()'
            ->with('event.course')
            ->first();
        echo "\$lastCompletedRegistration: " . $lastCompletedRegistration . "\n\n";

        // Step 2: Get the student's last attempt for the current course (incomplete or completed)
        $lastAttemptOnSameCourse = Registration::where('student_id', $student->id)
            ->where('event.course_id', $nextCourse->id)  // Same course as the current course
            ->orderByDesc('event_id')
            ->first();

        // Step 3: Determine which date to use as the baseline for the next event selection
        $baselineDate = null;

        if ($lastAttemptOnSameCourse) {
            // If there's a previous attempt (whether completed or not), use its 'dateto'
            $baselineDate = $lastAttemptOnSameCourse->event->dateto;
            echo "Last attempt for the same course was on: $baselineDate\n";
        } elseif ($lastCompletedRegistration) {
            // Otherwise, use the 'dateto' of the last completed course for regular progression
            $baselineDate = $lastCompletedRegistration->event->dateto;
            echo "Last completed course ended on: $baselineDate\n";
        }

        $completedRegistrations = Registration::where('student_id', $student->id)
            ->where('end_status', 'completed')
            ->orderByDesc('event_id') // Orders all results by 'event_id' in descending order
            ->with('event.course')    // Eager load the related event and course
            ->get();  // Fetch all matching records
        echo "\$completedRegistrations: " . json_encode($completedRegistrations, JSON_PRETTY_PRINT) . "\n\n";

        // Get the date of the last completed event (if any) ... this is now $baselineDate
        // $lastCompletedDate = $lastCompletedRegistration
        //     ? $lastCompletedRegistration->event->dateto
        //     : null;
        // echo "\$lastCompletedDate: " . $lastCompletedDate . "\n\n";

        // Extracts the course code from the $lastCompletedRegistration
        $lastCompletedCourseCode = $lastCompletedRegistration
            ? $lastCompletedRegistration->event->course->course_code
            : null;
        echo "\$lastCompletedCourseCode: " . $lastCompletedCourseCode . "\n\n";

        // Get the highest index of all this student's completed courses in the sequence
        $currentCourseIndex = $lastCompletedCourseCode // Actually course code ... not title! Happens to match code ID AND array index number in some cases.
            ? array_search($lastCompletedCourseCode, $this->courseSequence)
            : 0; // If no course completed, start with the first course
        echo "\$currentCourseIndex: " . $currentCourseIndex . "\n\n"; // This is an array number, NOT a course code!


        // Determine the next course
        if ($allCoursesCompleted) {
            // If all courses are completed, allow repeating any previous course except the one with ID 0
            $repeatableCourses = array_diff($completedCourseCodes, ['ATB K-BT']);
            $nextCourseCode = $this->faker->randomElement($repeatableCourses);
        } else {
            // Otherwise, find the next course in the sequence that hasn't been completed yet
            $nextCourseCode = null;
            for ($i = $currentCourseIndex + 1; $i < count($this->courseSequence); $i++) {
                if (!in_array($this->courseSequence[$i], $completedCourseCodes)) {
                    $nextCourseCode = $this->courseSequence[$i];
                    break;
                }
            }
        }

        // If no further courses are available, return empty array (no registration)
        if (!$nextCourseCode) {
            return [];
        }

        // Find the next course in the database
        $nextCourse = Course::where('course_code', $nextCourseCode)->first();

        // Step 1: Get the registered events with their event ID, title, and course code
        $registeredEvents = Registration::where('student_id', $student->id)
            ->with('event.course')  // Eager load event and course relationships
            ->get()  // Retrieve the results as a collection
            ->map(function ($registration) {
                return [
                    'event_id' => $registration->event->id,
                    'title' => $registration->event->title,
                    'course_code' => $registration->event->course->course_code,
                ];
            })
            ->toArray();
        echo "\$registeredEvents: " . json_encode($registeredEvents, JSON_PRETTY_PRINT) . "\n\n";

        // Step 2: Extract only event IDs to pass to the whereNotIn() query
        $registeredEventIds = array_column($registeredEvents, 'event_id');

        // Find the next available event for this course ... Part II
        $events = Event::where('course_id', $nextCourse->id)
            ->whereNotIn('id', $registeredEvents) // Exclude already registered events
            ->when($baselineDate, function ($query) use ($baselineDate) {
                // Ensure the event starts after the last completed event's 'dateto'
                return $query->where('datefrom', '>', $baselineDate);
            })
            ->orderBy('datefrom', 'asc') // Sort by the soonest available event
            ->get();

        // For debugging > output the list of available events after filtering and sorting
        echo "All Events for Course '{$nextCourse->course_code}':\n";
        $eventDetails = $events->pluck('id', 'course_code', 'datefrom')->toArray();
        print_r($eventDetails);

        echo "Available Events for course '{$nextCourse->course_title}' after '{$baselineDate}':\n";
        foreach ($events as $event) {
            echo "Event ID: {$event->id}, {$nextCourse->course_code} Starts on: {$event->datefrom}, Participants: {$event->participant_count}\n";
        }
        echo "\n";

        // Loop through the events and pick the first one with available seats
        $nextEvent = null;
        foreach ($events as $event) {
            // echo "Checking event ID: {$event->id}, Date: {$event->datefrom}, Participants: {$event->participant_count}\n";
            if ($event->participant_count < 20) {
                $nextEvent = $event;
                break;
            }
        }
        echo "\$nextEvent: " . $nextEvent . "\n\n";

        // If no event is available, return no registration
        if (!$nextEvent) {
            echo "No available events found for the course '{$nextCourse->course_title}'\n\n";
            return [];
        }


        // Update the participant count for the selected event
        $nextEvent->increment('participant_count');
        echo "Next event selected: Event ID {$nextEvent->id}, starting on {$nextEvent->datefrom}\n\n";
        echo "Participant count: " . $nextEvent->participant_count . "\n\n";
        echo "========================================================================\n\n";

        // Return the registration details for the next event the student will attend
        return [
            'student_id' => $student->id,
            'event_id' => $nextEvent->id,
            'end_status' => $this->faker->randomElement(['completed', 'incomplete']),
            'comments' => $this->faker->optional()->sentence(),
        ];
    }
}
