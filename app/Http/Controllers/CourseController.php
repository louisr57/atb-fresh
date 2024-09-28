<?php

namespace App\Http\Controllers;

use App\Models\Course; // Assuming your model is named 'Course'
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        // Fetch all courses from the database
        $courses = Course::all();

        // Pass courses to the view
        return view('courses.index', compact('courses'));
    }
}
