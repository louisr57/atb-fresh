<?php

namespace App\Http\Controllers;

use App\Models\Facilitator;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FacilitatorController extends Controller
{
    // Display a listing of facilitators
    public function index()
    {
        // Get all facilitators
        $facilitators = Facilitator::all();

        // Return view with facilitators
        return view('facilitators.index', compact('facilitators'));
    }

    // Show the details of a specific facilitator
    public function show($id)
    {
        // Find the facilitator by ID and load related events and courses
        $facilitator = Facilitator::with(['events' => function ($query) {
            $query->orderBy('datefrom', 'asc'); // Adjust 'start_date' to your actual date column name
        }])->findOrFail($id);

        // Return view with facilitator data
        return view('facilitators.show', compact('facilitator'));
    }

    public function create()
    {
        return view('facilitators.create');
    }

    public function store(Request $request)
    {
        $currentYear = now()->year;
        $minBirthYear = $currentYear - 100;
        $maxBirthYear = $currentYear - 18;

        // Validate the form inputs
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'nullable|string|max:10',
            'email' => 'required|email|unique:facilitators,email',
            'phone_number' => 'nullable|string|max:20',
            'dob' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($minBirthYear, $maxBirthYear) {
                    if ($value) {
                        $dob = \Carbon\Carbon::parse($value);
                        if ($dob->year < $minBirthYear || $dob->year > $maxBirthYear) {
                            $fail("The date of birth must be between {$minBirthYear} and {$maxBirthYear}.");
                        }
                    }
                },
            ],
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'post_code' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255'
        ]);


        // Create a new facilitator record
        $facilitator = Facilitator::create($validatedData);

        // Redirect back to a page (e.g., facilitator index) with a success message
        return redirect()->route('facilitators.show', $facilitator->id)->with('success', 'Facilitator created successfully.');
    }

    public function edit(Facilitator $facilitator)
    {
        return view('facilitators.edit', compact('facilitator'));
    }

    public function update(Request $request, Facilitator $facilitator)
    {

        $currentYear = now()->year;
        $minBirthYear = $currentYear - 100;
        $maxBirthYear = $currentYear - 18;

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'nullable|string|max:10',
            'email' => 'required|email|unique:facilitators,email,' . $facilitator->id,
            'phone_number' => 'nullable|string|max:20',
            'dob' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($minBirthYear, $maxBirthYear) {
                    if ($value) {
                        $dob = \Carbon\Carbon::parse($value);
                        if ($dob->year < $minBirthYear || $dob->year > $maxBirthYear) {
                            $fail("The date of birth must be between {$minBirthYear} and {$maxBirthYear}.");
                        }
                    }
                },
            ],
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'post_code' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255'
        ]);

        $facilitator->update($validatedData);

        return redirect()->route('facilitators.show', $facilitator)->with('success', 'Facilitator updated successfully.');
    }

    public function destroy($id)
    {
        $facilitator = Facilitator::findOrFail($id);

        // The if will never run because the delete button is disabled in the view
        if ($facilitator->events->isNotEmpty()) {
            return redirect()->route('facilitators.show', $facilitator->id)
                ->with('error', 'Cannot delete facilitator with associated events.');
        }

        $facilitator->delete();

        return redirect()->route('facilitators.index')
            ->with('success', 'Facilitator deleted successfully.');
    }
}
