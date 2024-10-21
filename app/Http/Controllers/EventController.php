<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Course;
use App\Models\Facilitator;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // Determine the column to sort by and the direction
        $sort_by = $request->get('sort_by', 'course_title'); // Default to sorting by course title
        $direction = $request->get('direction', 'asc'); // Default to ascending order

        // Map sortable columns to their respective database columns
        $sortableColumns = [
            'course_title' => 'courses.course_title',
            'participant_count' => 'events.participant_count',
            'datefrom' => 'events.datefrom',
            'dateto' => 'events.dateto',
            'facilitator_name' => 'facilitators.first_name',
        ];

        // Default to 'course_title' if the provided column is not in the sortable list
        $sortColumn = $sortableColumns[$sort_by] ?? 'courses.course_title';

        // Fetch events with related data, sorted by the chosen column, and with participant count
        $events = Event::with(['course', 'facilitator'])
            ->withCount('registrations') // Count the number of participants for each event
            ->join('courses', 'events.course_id', '=', 'courses.id')
            ->join('facilitators', 'events.facilitator_id', '=', 'facilitators.id')
            ->orderBy($sortColumn, $direction)
            ->paginate(15);

        // Pass the sorting parameters to the view
        return view('events.index', compact('events', 'sort_by', 'direction'));
    }

    public function show($id)
    {
        // Find the event by its ID, and load related registrations and student details
        $event = Event::with(['course', 'facilitator', 'registrations' => function ($query) {
            $query->join('students', 'students.id', '=', 'registrations.student_id')
                ->orderBy('students.last_name');
        }])
            ->findOrFail($id);

        // Return the view with the event data
        return view('events.show', compact('event'));
    }

    public function create()
    {
        $courses = Course::all();
        $facilitators = Facilitator::all();

        return view('events.create', compact('courses', 'facilitators'));
    }

    // Store the event data
    public function store(Request $request)
    {
        // Validate the form input
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'datefrom' => 'required|date',
            'dateto' => 'required|date|after_or_equal:datefrom',
            'course_id' => 'required|exists:courses,id',
            'facilitator_id' => 'required|exists:facilitators,id',
            'venue' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        // Create and store the new event
        Event::create($validatedData);

        return redirect()->route('events.create')->with('success', 'Event created successfully.');
    }
}
