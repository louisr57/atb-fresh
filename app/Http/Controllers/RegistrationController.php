<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Student;
use App\Models\Registration;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        // Determine the column to sort by and the direction
        $sort_by = $request->get('sort_by', 'registrations.id'); // Default to sorting by student name
        $direction = $request->get('direction', 'asc'); // Default to ascending order

        // Map sortable columns to their respective database columns
        $sortableColumns = [
            'student_name' => 'students.first_name',
            'course_name' => 'courses.course_title',
            'datefrom' => 'events.datefrom',
            'dateto' => 'events.dateto',
            'facilitator_name' => 'facilitators.first_name',
            'end_status' => 'registrations.end_status',
        ];

        // Get the sort column from the mapping, or use default
        $sortColumn = $sortableColumns[$sort_by] ?? 'students.first_name';

        $registrations = Registration::with(['student', 'event.course', 'event.facilitators'])
            ->join('students', 'registrations.student_id', '=', 'students.id')
            ->join('events', 'registrations.event_id', '=', 'events.id')
            ->join('courses', 'events.course_id', '=', 'courses.id')
            ->join('event_facilitator', 'events.id', '=', 'event_facilitator.event_id')
            ->join('facilitators', 'event_facilitator.facilitator_id', '=', 'facilitators.id')
            ->select('registrations.*')
            ->distinct()
            ->orderBy($sortColumn, $direction)
            ->when($sortColumn !== 'events.datefrom', function($query) {
                $query->orderBy('events.datefrom', 'asc');
            })
            ->paginate(30);

        // $registrations = Registration::with(['student', 'event.course', 'event.facilitators'])
        //     ->orderBy($sortColumn, $direction)
        //     ->paginate(50);

        // Pass the sorting parameters to the view
        return view('registrations.index', compact('registrations', 'sort_by', 'direction')); // , 'sort_by', 'direction'
    }


    public function show(Registration $registration)
    {
        // Load related data for a single registration
        $student = $registration->student; // Make sure it correctly loads the associated student
        $registration->load('student'); // Eager load student relationship

        return view('registrations.show', compact('registration', 'student'));
    }

    public function create(Event $event)
    {
        $students = Student::orderBy('first_name')->get();
        return view('registrations.create', compact('event', 'students'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'event_id' => 'required|exists:events,id',
            'student_id' => 'required|exists:students,id',
            'end_status' => 'required|string'
        ]);

        Registration::create($validatedData);

        return redirect()->route('events.show', $request->event_id)
            ->with('success', 'Participant added successfully');
    }
}


    // You can add methods for create, store, update, and delete if needed
