<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $sort_by = $request->get('sort_by', 'first_name');  // Default sort column
        $direction = $request->get('direction', 'asc');    // Default sort direction

        $students = Student::orderBy($sort_by, $direction)->paginate(50); // Paginate for ease
        //dd($students);

        return view('students.index', compact('students', 'sort_by', 'direction'));
    }

    public function show(Student $student)
    {
        dd($student);
        return view('students.show', compact('student'));
    }
}
