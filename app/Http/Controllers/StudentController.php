<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Rinvex\Country\CountryLoader; // If you're using the Laravel package for countries
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $sort_by = $request->get('sort_by', 'first_name');  // Default sort column
        $direction = $request->get('direction', 'asc');    // Default sort direction

        $students = Student::orderBy($sort_by, $direction)->paginate(30); // Paginate for ease
        //dd($students);

        return view('students.index', compact('students', 'sort_by', 'direction'));
    }

    public function show($id)
    {
        // Find the student by ID and load related registrations, events, courses, and facilitators
        $student = Student::with('registrations.event.course', 'registrations.event.facilitator')->findOrFail($id);

        // dd($student->dob);
        return view('students.show', compact('student'));
    }

    // Show the form for creating a new student
    public function create()
    {
        // If using the Rinvex package to fetch countries
        $countries = CountryLoader::countries(); // Fetch all countries

        return view('students.create', compact('countries'));
    }

    // Store a new student in the database
    public function store(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students',
            'phone_number' => 'required|string|max:20|unique:students',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'post_code' => 'required|string|max:20',
        ]);

        // Create a new student
        $student = Student::create($validated);

        // Get the sorting and direction from the request (or default to first_name asc)
        $sort_by = $request->get('sort_by', 'first_name');
        $direction = $request->get('direction', 'asc');

        // Count how many students come before the newly created student in the sorted list
        $studentCountBefore = Student::where($sort_by, '<', $student->$sort_by)
            ->orWhere(function ($query) use ($sort_by, $student) {
                $query->where($sort_by, '=', $student->$sort_by)
                    ->where('id', '<', $student->id);  // In case of tie, use 'id' for a stable sort
            })
            ->orderBy($sort_by, $direction)
            ->count();

        // Calculate which page the student is on
        $perPage = 30; // Assuming pagination is set to 20
        $page = ceil(($studentCountBefore + 1) / $perPage);

        // Redirect to the calculated page, with the newly created student's ID in the query string
        return redirect()->route('students.index', ['page' => $page, 'highlight' => $student->id, 'sort_by' => $sort_by, 'direction' => $direction])
            ->with('success', 'Student created successfully.');
    }


    public function edit(Student $student)
    {
        // If using the Rinvex package to fetch countries
        $countries = CountryLoader::countries(); // Fetch all countries

        return view('students.edit', compact('student', 'countries'));
    }

    public function update(Request $request, Student $student)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students,email,' . $student->id,
            'phone_number' => 'required|string|max:20',
            'dob' => 'required|date',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'post_code' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
            'ident' => 'nullable|string|max:255',
            'next_of_kin' => 'nullable|string|max:255',
            'allergies' => 'nullable|string|max:255',
            'special_needs' => 'nullable|string|max:1020',
        ]); // Very important to include all fields here that we want to validate and send to the view and database.

        if ($validator->fails()) {
            return redirect()
                ->route('students.edit', $student->id)
                ->withErrors($validator)
                ->withInput();
        }

        $student->update($validator->validated());

        return redirect()
            ->route('students.show', $student->id)
            ->with('success', 'Student updated successfully!');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        // The if will never run because the delete button is disabled in the view
        if ($student->events->isNotEmpty()) {
            return redirect()->route('facilitators.show', $student->id)
                ->with('error', 'Cannot delete facilitator with associated events.');
        }

        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }
}
