<x-layout>

    <x-slot:heading>
        Event Details
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">{{ $event->course->course_title }}</h1>

        <p class="text-lg mb-2">
            <strong>Date:</strong> {{ $event->datefrom }} to {{ $event->dateto ?? 'N/A' }}
        </p>
        <p class="text-lg mb-4">
            <strong>Instructor:</strong> {{ $event->instructor->first_name }} {{ $event->instructor->last_name }}
        </p>

        @if ($event->registrations->isNotEmpty())
        <h2 class="text-2xl font-bold mb-4">Registered Participants</h2>

        <!-- Participants table -->
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2">Full Name</th>
                        <th class="border px-4 py-2">Address</th>
                        <th class="border px-4 py-2">Phone Number</th>
                        <th class="border px-4 py-2">City</th>
                        <th class="border px-4 py-2">Country</th>
                        <th class="border px-4 py-2">Date Registered</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($event->registrations as $registration)
                    <tr class="hover:bg-gray-100">
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $registration->student->first_name }} {{ $registration->student->last_name }}
                        </td>
                        <td class="border px-4 py-2">{{ $registration->student->address }}</td>
                        <td class="border px-4 py-2">{{ $registration->student->phone_number }}</td>
                        <td class="border px-4 py-2">{{ $registration->student->city }}</td>
                        <td class="border px-4 py-2">{{ $registration->student->country }}</td>
                        <td class="border px-4 py-2">{{ $registration->created_at->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-lg text-gray-600">No participants have registered for this event yet.</p>
        @endif

        <!-- Back button -->
        <div class="mt-4">
            <a href="{{ route('events.index') }}" class="text-blue-600 hover:underline">← Back to Events List</a>
        </div>
    </div>

</x-layout>