<?php

use App\Models\Course;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Venue;

function makeRegistrationForSoftDeleteTest(): Registration
{
    $student = Student::factory()->create();

    $course = Course::create([
        'course_code' => 'TEST-1',
        'course_title' => 'Test Course',
        'description' => 'Test',
        'prerequisites' => 'none',
        'duration' => 1,
    ]);

    $venue = Venue::factory()->create();

    $event = Event::create([
        'title' => 'Test Event',
        'datefrom' => now()->toDateString(),
        'dateto' => now()->addDay()->toDateString(),
        'timefrom' => '09:00',
        'timeto' => '17:00',
        'venue_id' => $venue->id,
        'course_id' => $course->id,
        'participant_count' => 0,
    ]);

    return Registration::create([
        'student_id' => $student->id,
        'event_id' => $event->id,
        'end_status' => 'registered',
        'comments' => null,
    ]);
}

test('deleting a student soft deletes it and cascades a soft delete to its registrations', function () {
    $registration = makeRegistrationForSoftDeleteTest();
    $student = $registration->student;

    $student->delete();

    expect(Student::find($student->id))->toBeNull();
    expect(Student::withTrashed()->find($student->id))->not->toBeNull();

    expect(Registration::find($registration->id))->toBeNull();
    expect(Registration::withTrashed()->find($registration->id))->not->toBeNull();
});

test('restoring a student restores its cascaded-deleted registrations', function () {
    $registration = makeRegistrationForSoftDeleteTest();
    $student = $registration->student;

    $student->delete();
    $student->refresh();
    $student->restore();

    expect(Student::find($student->id))->not->toBeNull();
    expect(Registration::find($registration->id))->not->toBeNull();
});

test('deleting an event soft deletes it and cascades a soft delete to its registrations', function () {
    $registration = makeRegistrationForSoftDeleteTest();
    $event = $registration->event;

    $event->delete();

    expect(Event::find($event->id))->toBeNull();
    expect(Event::withTrashed()->find($event->id))->not->toBeNull();

    expect(Registration::find($registration->id))->toBeNull();
    expect(Registration::withTrashed()->find($registration->id))->not->toBeNull();
});

test('deleting a registration directly soft deletes it only', function () {
    $registration = makeRegistrationForSoftDeleteTest();

    $registration->delete();

    expect(Registration::find($registration->id))->toBeNull();
    expect(Registration::withTrashed()->find($registration->id))->not->toBeNull();
    expect(Student::find($registration->student_id))->not->toBeNull();
    expect(Event::find($registration->event_id))->not->toBeNull();
});
