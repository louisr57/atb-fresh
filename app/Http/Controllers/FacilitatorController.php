<?php

namespace App\Http\Controllers;

use App\Models\facilitator;
use Illuminate\Http\Request;

class facilitatorController extends Controller
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
        $facilitator = Facilitator::with('events.course')->findOrFail($id);

        // Return view with facilitator data
        return view('facilitators.show', compact('facilitator'));
    }

    public function store(Request $request)
    {
        // Validate the form inputs
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:facilitators,email',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'post_code' => 'nullable|string|max:20',
        ]);

        // Create a new facilitator record
        Facilitator::create($validatedData);

        // Redirect back to a page (e.g., facilitator index) with a success message
        return redirect()->route('facilitators.index')->with('success', 'facilitator created successfully.');
    }
}
