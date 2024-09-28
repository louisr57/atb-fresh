<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        // Determine the column to sort by and the direction
        $sort_by = $request->get('sort_by', 'student_name'); // Default to sorting by student name
        $direction = $request->get('direction', 'asc'); // Default to ascending order

        // Map sortable columns to their respective database columns
        $sortableColumns = [
            'student_name' => 'students.first_name',
            'course_name' => 'courses.course_title',
            'datefrom' => 'events.datefrom',
            'dateto' => 'events.dateto',
            'instructor_name' => 'instructors.first_name',
            'end_status' => 'registrations.end_status',
        ];

        // Default to 'student_name' if the provided column is not in the sortable list
        $sortColumn = $sortableColumns[$sort_by] ?? 'students.first_name';

        // Fetch registrations with relationships, sorted by the chosen column
        $registrations = Registration::with(['student', 'event.course', 'event.instructor'])
            ->join('students', 'registrations.student_id', '=', 'students.id')
            ->join('events', 'registrations.event_id', '=', 'events.id')
            ->join('courses', 'events.course_id', '=', 'courses.id')
            ->join('instructors', 'events.instructor_id', '=', 'instructors.id')
            ->orderBy($sortColumn, $direction)
            ->paginate(50);

        // Pass the sorting parameters to the view
        return view('registrations.index', compact('registrations', 'sort_by', 'direction'));
    }

    public function show(Registration $registration)
    {
        // Load related data for a single registration
        $registration->load(['student', 'event.course', 'event.instructor']);

        return view('registrations.show', compact('registration'));
    }
}


    // You can add methods for create, store, update, and delete if needed
