<?php

namespace App\Http\Controllers;

use App\Models\Facilitator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rinvex\Country\CountryLoader; // If you're using the Laravel package for countries

class FacilitatorController extends Controller
{
    // Display a listing of facilitators
    public function index(Request $request)
    {
        $sort_by = $request->get('sort_by', 'id');  // Default sort column
        $direction = $request->get('direction', 'asc');    // Default sort direction
        // Get all facilitators
        $facilitators = (new Facilitator)->orderBy($sort_by, $direction)->paginate(20); // Paginate for ease

        // Return view with facilitators
        return view('facilitators.index', compact('facilitators', 'sort_by', 'direction'));
    }

    // Show the details of a specific facilitator
    public function show(Request $request, $id)
    {
        // Get sort parameters from request
        $sort_by = $request->get('sort', 'datefrom');
        $direction = $request->get('direction', 'asc');

        // Find the facilitator by ID
        $facilitator = (new Facilitator)->findOrFail($id);

        // Build the events query
        $eventsQuery = $facilitator->events()->withCount('registrations');

        // Handle different sort columns
        switch ($sort_by) {
            case 'course_title':
                $eventsQuery->join('courses', 'events.course_id', '=', 'courses.id')
                    ->orderBy('courses.course_title', $direction)
                    ->withCount('registrations')
                    ->select('events.*');
                break;
            case 'participant_count':
                $eventsQuery->orderBy('registrations_count', $direction);
                break;
            case 'venue':
                $eventsQuery->join('venues', 'events.venue_id', '=', 'venues.id')
                    ->orderBy('venues.venue_name', $direction)
                    ->select('events.*');
                break;
            case 'city':
                $eventsQuery->join('venues', 'events.venue_id', '=', 'venues.id')
                    ->orderBy('venues.city', $direction)
                    ->select('events.*');
                break;
            case 'state':
                $eventsQuery->join('venues', 'events.venue_id', '=', 'venues.id')
                    ->orderBy('venues.state', $direction)
                    ->select('events.*');
                break;
            case 'country':
                $eventsQuery->join('venues', 'events.venue_id', '=', 'venues.id')
                    ->orderBy('venues.country', $direction)
                    ->select('events.*');
                break;
            default:
                $eventsQuery->orderBy($sort_by, $direction);
        }

        // Get paginated events with relationships
        $events = $eventsQuery->with(['course', 'venue'])->paginate(10);

        activity('facilitator') // Explicitly set the log name to 'facilitator'
            ->performedOn($facilitator)
            ->causedBy(Auth::user())
            ->log('Facilitator profile viewed');

        // Return view with facilitator data
        return view('facilitators.show', compact('facilitator', 'events', 'sort_by', 'direction'));
    }

    public function create()
    {
        // If using the Rinvex package to fetch countries
        $countries = CountryLoader::countries(); // Fetch all countries

        return view('facilitators.create', compact('countries'));
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
            'website' => 'nullable|url|max:255',
        ]);

        // Create a new facilitator record
        $facilitator = (new Facilitator)->create($validatedData);

        // Redirect back to a page (e.g., facilitator index) with a success message
        return redirect()->route('facilitators.show', $facilitator->id)->with('success', 'Facilitator created successfully.');
    }

    public function edit(Facilitator $facilitator)
    {
        // If using the Rinvex package to fetch countries
        $countries = CountryLoader::countries(); // Fetch all countries

        return view('facilitators.edit', compact('facilitator', 'countries'));
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
            'email' => 'required|email|unique:facilitators,email,'.$facilitator->id,
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
            'website' => 'nullable|url|max:255',
        ]);

        $facilitator->update($validatedData);

        return redirect()->route('facilitators.show', $facilitator)->with('success', 'Facilitator updated successfully.');
    }

    public function destroy($id)
    {
        $facilitator = (new Facilitator)->findOrFail($id);

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
