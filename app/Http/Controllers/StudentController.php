<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Rinvex\Country\CountryLoader;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function index()
    {
        return view('students.index', ['direction' => request('direction', 'asc')]);
    }

    public function show($id)
    {
        $student = Student::with('registrations.event.course', 'registrations.event.facilitator')->findOrFail($id);
        return view('students.show', compact('student'));
    }

    public function create()
    {
        $countries = CountryLoader::countries();
        return view('students.create', compact('countries'));
    }

    public function store(Request $request)
    {
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

        $student = Student::create($validated);

        $sort_by = $request->get('sort_by', 'first_name');
        $direction = $request->get('direction', 'asc');

        $studentCountBefore = Student::where($sort_by, '<', $student->$sort_by)
            ->orWhere(function ($query) use ($sort_by, $student) {
                $query->where($sort_by, '=', $student->$sort_by)
                    ->where('id', '<', $student->id);
            })
            ->orderBy($sort_by, $direction)
            ->count();

        $perPage = 10;
        $page = ceil(($studentCountBefore + 1) / $perPage);

        return redirect()->route('students.index', [
            'page' => $page,
            'highlight' => $student->id,
            'sort_by' => $sort_by,
            'direction' => $direction
        ])->with('success', 'Student created successfully.');
    }

    public function edit(Student $student)
    {
        $countries = CountryLoader::countries();
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
        ]);

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

        if ($student->events->isNotEmpty()) {
            return redirect()->route('facilitators.show', $student->id)
                ->with('error', 'Cannot delete facilitator with associated events.');
        }

        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }
}
