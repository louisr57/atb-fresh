<x-layout>
    <x-slot:heading>
        Venue Details
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">{{ $venue->venue_name }}</h1>
            @if($venue->events->isEmpty())
            <form action="{{ route('venues.destroy', $venue->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                    onclick="return confirm('Are you sure you want to delete this venue?');">
                    Delete Venue
                </button>
            </form>
            @endif
            <a href="{{ route('venues.edit', $venue->id) }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Edit Venue
            </a>
        </div>

        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Venue Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Venue Name</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $venue->venue_name }}
                    </p>
                </div>

                <!-- Address -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Address</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $venue->address }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <!-- City -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">City</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $venue->city }}
                    </p>
                </div>

                <!-- State -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">State</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $venue->state }}
                    </p>
                </div>

                <!-- Postcode -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Postcode</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $venue->postcode }}
                    </p>
                </div>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Country -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Country</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $venue->country }}
                    </p>
                </div>

                <!-- Location Geocode -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Location Geocode</label>
                <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                    {{ $venue->location_geocode ?? 'N/A' }}
                </p>
            </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                 <!-- Contact Name -->
                 <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Contact Name</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $venue->vcontact_person }}
                    </p>
                </div>

                <!-- Phone -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Phone</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $venue->vcontact_phone }}
                    </p>
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Email</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $venue->vcontact_email }}
                    </p>
                </div>
        </div>

            <div class="grid grid-cols-1 gap-4">
                <!-- Remarks -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Remarks</label>
                    <p
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200 h-24 overflow-y-auto">
                        {{ $venue->remarks ?? 'No remarks' }}
                    </p>
                </div>
            </div>
        </div>
        </div>

        @if($venue->events->isNotEmpty())
        <h2 class="text-2xl font-bold mb-5 mt-5">Events at this Venue</h2>
        <table class="min-w-full table-auto border-collapse border border-gray-500">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border border-gray-500 px-4 py-2">Event Title</th>
                    <th class="border border-gray-500 px-4 py-2">Course</th>
                    <th class="border border-gray-500 px-4 py-2">Start Date</th>
                    <th class="border border-gray-500 px-4 py-2">End Date</th>
                    <th class="border border-gray-500 px-4 py-2">Facilitator</th>
                </tr>
            </thead>
            <tbody class="bg-gray-50">
                @foreach($venue->events as $event)
                <tr class="hover:bg-sky-100 cursor-pointer" onclick="window.location='{{ route('events.show', $event->id) }}'">
                    <td class="border border-gray-500 px-4 py-2">{{ $event->title }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $event->course->course_title }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $event->datefrom }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $event->dateto }}</td>
                    <td class="border border-gray-500 px-4 py-2">
                        @forelse ($event->facilitators as $facilitator)
                            {{ $facilitator->first_name }} {{ $facilitator->last_name }}
                            @unless ($loop->last), @endunless
                        @empty
                            No facilitator assigned
                        @endforelse
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-lg text-gray-600">No events have been scheduled at this venue yet.</p>
        @endif

        <div class="mt-6">
            <a href="{{ route('venues.index') }}" class="text-blue-600 hover:underline">← Back to Venues List</a>
        </div>
    </div>
</x-layout>
