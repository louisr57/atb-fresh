<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Rinvex\Country\CountryLoader; // If you're using the Laravel package for countries


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

        return view('students.show', compact('student'));
    }

    // Show the form for creating a new student
    public function create()
    {
        // If using the Rinvex package to fetch countries
        $countries = CountryLoader::countries(); // Fetch all countries

        // If using a static list, you can also define the $countries array manually here

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
}
