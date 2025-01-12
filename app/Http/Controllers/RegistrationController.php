<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->select(
                'registrations.*',
                'students.first_name',
                'students.last_name',
                'events.datefrom',
                'events.dateto',
                'courses.course_title',
                'facilitators.first_name as facilitator_first_name'
            )
            ->distinct()
            ->orderBy($sortColumn, $direction)
            ->when($sortColumn !== 'events.datefrom', function ($query) {
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

        activity('registration') // Explicitly set the log name to 'registration'
            ->performedOn($registration)
            ->causedBy(Auth::user())
            ->log('Registration profile viewed');

        return view('registrations.show', compact('registration', 'student'));
    }

    public function create(Event $event)
    {
        $students = Student::query()->orderBy('first_name')->get();

        return view('registrations.create', compact('event', 'students'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'event_id' => 'required|exists:events,id',
            'student_id' => 'required|exists:students,id',
            'end_status' => 'required|string',
        ]);

        $registration = new Registration($validatedData);
        $registration->save();

        return redirect()->route('events.show', $request->event_id)
            ->with('success', 'Participant added successfully');
    }

    public function update(Request $request, Registration $registration)
    {
        \Log::info('Update request received', [
            'request_data' => $request->all(),
            'registration_id' => $registration->id,
            'is_ajax' => $request->ajax(),
            'content_type' => $request->header('Content-Type')
        ]);

        try {
            $validatedData = $request->validate([
                'end_status' => 'required|in:Registered,Withdrawn,Completed,Incomplete',
                'comments' => 'nullable|string'
            ]);

            \Log::info('Validated data', ['data' => $validatedData]);

            $registration->fill($validatedData);
            $saved = $registration->save();

            \Log::info('Save attempt result', [
                'saved' => $saved,
                'registration' => $registration->toArray()
            ]);

            if (!$saved) {
                throw new \Exception('Failed to save registration');
            }

            $registration->refresh();

            if ($request->ajax()) {
                $response = [
                    'success' => true,
                    'message' => 'Registration updated successfully',
                    'registration' => $registration->toArray()
                ];
                \Log::info('Sending JSON response', $response);
                return response()->json($response);
            }

            return back()->with('success', 'Registration updated successfully');
        } catch (\Exception $e) {
            \Log::error('Error updating registration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'registration_id' => $registration->id
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating registration: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error updating registration');
        }
    }
}
