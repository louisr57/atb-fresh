<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Rinvex\Country\CountryLoader;

class StudentController extends Controller
{
    public function index()
    {
        return view('students.index', ['direction' => request('direction', 'asc')]);
    }

    public function show($id)
    {
        $student = Student::with('registrations.event.course', 'registrations.event.facilitators')->findOrFail($id);
        activity('student') // Explicitly set the log name to 'student'
            ->performedOn($student)
            ->causedBy(Auth::user())
            ->log('Student profile viewed');

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
            'phone_number' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'post_code' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'dob' => 'nullable|date',
            'ident' => 'nullable|string|max:255',
            'next_of_kin' => 'nullable|string|max:255',
            'allergies' => 'nullable|string|max:255',
            'special_needs' => 'nullable|string|max:1020',
        ]);

        $student = Student::create($validated);
        //The warning "Non static method 'create' should not be called statically" is a false positive from the IDE.
        // In Laravel, the create() method is inherited from the Eloquent Model class and is designed to be called statically.
        // The current usage Student::create($validated) is the correct way to create new model instances in Laravel.
        // This is a common pattern in Laravel's Eloquent ORM, and you can safely ignore this IDE warning. The code is working as intended.

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
            'direction' => $direction,
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
            'email' => 'required|string|email|max:255|unique:students,email,'.$student->id,
            'phone_number' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'post_code' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
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

        $oldData = $student->toArray();
        $student->update($validator->validated());
        // No need log the update of a student as it is already logged in the model
        // activity()
        //     ->performedOn($student)
        //     ->causedBy(Auth::user())
        //     ->withProperties([
        //         'old' => $oldData,
        //         'new' => $student->toArray(),
        //         'performed_by' => Auth::user()->name
        //     ])
        //     ->log('Student information updated');

        return redirect()
            ->route('students.show', $student->id)
            ->with('success', 'Student updated successfully!');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        if ($student->registrations->isNotEmpty()) {
            return redirect()->route('students.show', $student->id)
                ->with('error', 'Cannot delete student with existing registrations.');
        }

        $studentData = $student->toArray();
        // No need log the deletion of a student as it is already logged in the model
        // activity()
        //     ->performedOn($student)
        //     ->causedBy(Auth::user())
        //     ->withProperties([
        //         'deleted_student' => [
        //             'id' => $student->id,
        //             'name' => $student->first_name . ' ' . $student->last_name,
        //             'email' => $student->email,
        //             'phone_number' => $student->phone_number,
        //             'address' => $student->address,
        //             'city' => $student->city,
        //             'state' => $student->state,
        //             'country' => $student->country,
        //             'post_code' => $student->post_code
        //         ],
        //         'performed_by' => Auth::user()->name
        //     ])
        //     ->log('Student record deleted: ' . $student->first_name . ' ' . $student->last_name);

        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }
}
