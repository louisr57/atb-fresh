<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    // Display a listing of instructors
    public function index()
    {
        // Get all instructors
        $instructors = Instructor::all();

        // Return view with instructors
        return view('instructors.index', compact('instructors'));
    }

    // Show the details of a specific instructor
    public function show($id)
    {
        // Find the instructor by ID and load related events and courses
        $instructor = Instructor::with('events.course')->findOrFail($id);

        // Return view with instructor data
        return view('instructors.show', compact('instructor'));
    }
}
