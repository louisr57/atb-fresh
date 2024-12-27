<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Course;
use App\Models\Facilitator;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'venue_name' => 'venues.venue_name',
        ];

        // Default to 'course_title' if the provided column is not in the sortable list
        $sortColumn = $sortableColumns[$sort_by] ?? 'courses.course_title';

        // Build the query
        $query = Event::query()
            ->join('courses', 'events.course_id', '=', 'courses.id')
            ->join('facilitators', 'events.facilitator_id', '=', 'facilitators.id')
            ->join('venues', 'events.venue_id', '=', 'venues.id')
            ->select('events.*')
            ->with(['course', 'facilitator', 'venue'])
            ->withCount('registrations');

        // Apply search filters
        if ($request->filled('search_course')) {
            $query->where('courses.course_title', 'like', '%' . $request->search_course . '%');
        }

        if ($request->filled('search_facilitator')) {
            $searchTerm = $request->search_facilitator;
            $query->where(function($q) use ($searchTerm) {
                $q->where(DB::raw("CONCAT(facilitators.first_name, ' ', facilitators.last_name)"), 'like', '%' . $searchTerm . '%')
                  ->orWhere(DB::raw("CONCAT(facilitators.last_name, ' ', facilitators.first_name)"), 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('search_date')) {
            $query->whereDate('events.datefrom', '=', $request->search_date);
        }

        if ($request->filled('search_venue')) {
            $query->where('venues.venue_name', 'like', '%' . $request->search_venue . '%');
        }

        if ($request->filled('search_city')) {
            $query->where('venues.city', 'like', '%' . $request->search_city . '%');
        }

        if ($request->filled('search_state')) {
            $query->where('venues.state', 'like', '%' . $request->search_state . '%');
        }

        if ($request->filled('search_country')) {
            $query->where('venues.country', 'like', '%' . $request->search_country . '%');
        }

        // Apply sorting and get paginated results
        $events = $query->orderBy($sortColumn, $direction)
                       ->paginate(15);

        // Pass the sorting parameters to the view
        return view('events.index', compact('events', 'sort_by', 'direction'));
    }

    public function show($id)
    {
        $event = Event::with(['course', 'facilitator', 'venue'])
            ->with(['registrations' => function ($query) {
                $query->select('registrations.*')
                    ->join('students', 'students.id', '=', 'registrations.student_id')
                    ->orderBy('students.last_name')
                    ->orderBy('registrations.id');
            }])
            ->findOrFail($id);

        return view('events.show', compact('event'));
    }

    public function create()
    {
        $courses = Course::all();
        $facilitators = Facilitator::all();
        $venues = Venue::all();

        return view('events.create', compact('courses', 'facilitators', 'venues'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'facilitator_id' => 'required|exists:facilitators,id',
            'venue_id' => 'required|exists:venues,id',
            'datefrom' => 'required|date',
            'dateto' => 'required|date|after_or_equal:datefrom',
            'timefrom' => 'required',
            'timeto' => 'required',
            'remarks' => 'nullable|string'
        ]);

        $event = Event::create($validatedData);

        return redirect()->route('events.show', $event->id)
            ->with('success', 'Event created successfully.');
    }

    public function edit(Event $event)
    {
        $courses = Course::all();
        $facilitators = Facilitator::all();
        $venues = Venue::all();
        return view('events.edit', compact('event', 'courses', 'facilitators', 'venues'));
    }

    public function update(Request $request, Event $event)
    {
        // eval(\Psy\sh());

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'facilitator_id' => 'required|exists:facilitators,id',
            'venue_id' => 'required|exists:venues,id',
            'datefrom' => 'required|date',
            'dateto' => 'required|date|after_or_equal:datefrom',
            'timefrom' => 'required',
            'timeto' => 'required',
            'remarks' => 'nullable|string'
        ]);


        $event->update($validatedData);
        return redirect()->route('events.show', $event->id)
            ->with('success', 'Event updated successfully.');
    }
}
