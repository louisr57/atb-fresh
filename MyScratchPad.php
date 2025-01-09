<?php ?>

<?php

// Step 1: Get the student's last completed registration for normal progression
$lastCompletedRegistration = Registration::where('student_id', $student->id)
    ->where('end_status', 'completed')
    ->orderByDesc('event_id')
    ->with('event.course')
    ->first();

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

// Step 4: Find the next available event for the current course that starts after the baseline date
$events = Event::where('course_id', $nextCourse->id)
    ->whereNotIn('id', $registeredEventIds)
    ->when($baselineDate, function ($query) use ($baselineDate) {
        // Ensure the event starts after the last attempt or last completed course
        return $query->where('datefrom', '>', $baselineDate);
    })
    ->orderBy('datefrom', 'asc')  // Sort by the soonest available event
    ->get();

echo "Available Events for the same course:\n";
foreach ($events as $event) {
    echo "Event ID: {$event->id}, Date: {$event->datefrom}, Participants: {$event->participant_count}\n";
}

// Step 5: Pick the first available event
$nextEvent = $events->first(function ($event) {
    return $event->participant_count < 30;
});

// If no event is found, return an empty array (no registration)
if (! $nextEvent) {
    echo "No available events found for the course '{$nextCourse->course_title}'\n";

    return [];
}

// Register for the next event
$nextEvent->increment('participant_count');
echo "Next event selected: Event ID {$nextEvent->id}, starting on {$nextEvent->datefrom}\n";

return [
    'student_id' => $student->id,
    'event_id' => $nextEvent->id,
    'end_status' => $this->faker->randomElement(['completed', 'incomplete']),
    'comments' => $this->faker->optional()->sentence(),
];
