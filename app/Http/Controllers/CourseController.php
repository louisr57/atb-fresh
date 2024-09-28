<?php

namespace App\Http\Controllers;

use App\Models\Course; // Assuming your model is named 'Course'
use Illuminate\Http\Request;

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

    public function show(Course $course)
    {
        return view('courses.show', compact('courses'));
    }
}
