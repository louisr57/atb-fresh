<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request; // Assuming your model is named 'Course'
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

// Following TODO extension terms are just for reminding me to use them in the future
// I've seen many places in the codebase where other authors have used these.
// FIXME: ... more details here ... I can also use NOTE, REVIEW, DEBUG, HACK, IDEA, or any other comment tags
// TODO: ... more details here ... see TODO extension for more details

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $sort_by = $request->get('sort_by', 'id');  // Default sort column
        $direction = $request->get('direction', 'asc');    // Default sort direction

        $courses = Course::orderBy($sort_by, $direction)->paginate(20); // Paginate for ease

        // Pass courses to the view
        return view('courses.index', compact('courses', 'sort_by', 'direction'));
    }

    public function show(Request $request, $id)
    {
        $sort_by = $request->get('sort_by', 'datefrom');  // Default sort by start date
        $direction = $request->get('direction', 'asc');    // Default sort direction

        // Fetch the course with its events and related models
        $course = Course::with(['events' => function ($query) use ($sort_by, $direction) {
            // Handle different sort columns
            switch ($sort_by) {
                case 'participant_count':
                    $query->orderBy('participant_count', $direction);
                    break;
                case 'facilitator':
                    $query->join('event_facilitator', 'events.id', '=', 'event_facilitator.event_id')
                        ->join('facilitators', 'event_facilitator.facilitator_id', '=', 'facilitators.id')
                        ->orderBy('facilitators.first_name', $direction)
                        ->orderBy('facilitators.last_name', $direction)
                        ->select('events.*')
                        ->distinct();
                    break;
                case 'venue':
                    $query->join('venues', 'events.venue_id', '=', 'venues.id')
                        ->orderBy('venues.venue_name', $direction)
                        ->select('events.*');
                    break;
                case 'city':
                    $query->join('venues', 'events.venue_id', '=', 'venues.id')
                        ->orderBy('venues.city', $direction)
                        ->select('events.*');
                    break;
                case 'state':
                    $query->join('venues', 'events.venue_id', '=', 'venues.id')
                        ->orderBy('venues.state', $direction)
                        ->select('events.*');
                    break;
                case 'country':
                    $query->join('venues', 'events.venue_id', '=', 'venues.id')
                        ->orderBy('venues.country', $direction)
                        ->select('events.*');
                    break;
                default:
                    $query->orderBy($sort_by, $direction);
            }
        }, 'events.facilitators', 'events.venue'])->findOrFail($id);

        activity('course') // Explicitly set the log name to 'course'
            ->performedOn($course)
            ->causedBy(Auth::user())
            ->log('Course profile viewed');

        // Return the course to the view with sorting parameters
        return view('courses.show', compact('course', 'sort_by', 'direction'));
    }

    public function create()
    {
        return view('courses.create');
    }

    public function store(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'course_code' => 'required|string|max:255|unique:courses',
            'course_title' => 'required|string|max:255',
            'description' => 'required|string',
            'prerequisites' => 'required|string',
            'duration' => 'required|numeric|min:0',
        ]);

        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()
                ->route('courses.create')
                ->withErrors($validator)
                ->withInput();
        }

        // Create a new course with the validated data
        $course = Course::create($validator->validated());

        // Redirect to the course's show page with a success message
        return redirect()
            ->route('courses.show', $course->id)
            ->with('success', 'Course created successfully!');
    }

    public function edit($id)
    {
        $course = Course::findOrFail($id);

        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validatedData = $request->validate([
            'course_code' => 'required|string|max:255|unique:courses,course_code,'.$id,
            'course_title' => 'required|string|max:255',
            'description' => 'required|string',
            'prerequisites' => 'required|string',
            'duration' => 'required|numeric|min:1',
        ]);

        $course->update($validatedData);

        return redirect()->route('courses.show', $course->id)->with('success', 'Course updated successfully.');
    }

    public function destroy($id)
    {
        // Find the course by ID
        $course = Course::findOrFail($id);

        // Perform the delete
        $course->delete();

        // Redirect back to the courses list with a success message
        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }
}
