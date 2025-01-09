<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Rinvex\Country\CountryLoader;

class VenueController extends Controller
{
    public function index(Request $request)
    {
        $sort_by = $request->get('sort_by', 'venue_name');  // Default sort column
        $direction = $request->get('direction', 'asc');    // Default sort direction

        $query = Venue::query();

        // Apply search filters
        if ($request->filled('search_venue')) {
            $query->where('venue_name', 'like', '%'.$request->search_venue.'%');
        }

        if ($request->filled('search_city')) {
            $query->where('city', 'like', '%'.$request->search_city.'%');
        }

        if ($request->filled('search_country')) {
            $query->where('country', 'like', '%'.$request->search_country.'%');
        }

        $venues = $query->orderBy($sort_by, $direction)->paginate(30);

        return view('venues.index', compact('venues', 'sort_by', 'direction'));
    }

    public function show($id)
    {
        $venue = Venue::with('events.course', 'events.facilitators')->findOrFail($id);

        return view('venues.show', compact('venue'));
    }

    public function create()
    {
        $countries = CountryLoader::countries();

        return view('venues.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'venue_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postcode' => 'required|string|max:20',
            'location_geocode' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $venue = Venue::create($validated);

        // Get the sorting and direction from the request
        $sort_by = $request->get('sort_by', 'venue_name');
        $direction = $request->get('direction', 'asc');

        // Calculate pagination for the newly created venue
        $venueCountBefore = Venue::where($sort_by, '<', $venue->$sort_by)
            ->orWhere(function ($query) use ($sort_by, $venue) {
                $query->where($sort_by, '=', $venue->$sort_by)
                    ->where('id', '<', $venue->id);
            })
            ->orderBy($sort_by, $direction)
            ->count();

        $perPage = 30;
        $page = ceil(($venueCountBefore + 1) / $perPage);

        return redirect()->route('venues.index', [
            'page' => $page,
            'highlight' => $venue->id,
            'sort_by' => $sort_by,
            'direction' => $direction,
        ])->with('success', 'Venue created successfully.');
    }

    public function edit(Venue $venue)
    {
        $countries = CountryLoader::countries();

        return view('venues.edit', compact('venue', 'countries'));
    }

    public function update(Request $request, Venue $venue)
    {
        $validator = Validator::make($request->all(), [
            'venue_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postcode' => 'required|string|max:20',
            'location_geocode' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('venues.edit', $venue->id)
                ->withErrors($validator)
                ->withInput();
        }

        $venue->update($validator->validated());

        return redirect()
            ->route('venues.show', $venue->id)
            ->with('success', 'Venue updated successfully!');
    }

    public function destroy($id)
    {
        $venue = Venue::findOrFail($id);

        if ($venue->events->isNotEmpty()) {
            return redirect()->route('venues.show', $venue->id)
                ->with('error', 'Cannot delete venue with associated events.');
        }

        $venue->delete();

        return redirect()->route('venues.index')
            ->with('success', 'Venue deleted successfully.');
    }
}
