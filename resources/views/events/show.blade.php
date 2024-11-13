<x-layout>
    <x-slot:heading>
        Event Details
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <!-- Success Message -->
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            {{ session('success') }}
        </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">{{ $event->title }}</h1>
            <div class="space-x-4">
                <a href="{{ route('registrations.create', ['event' => $event->id]) }}"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Add Participant
                </a>
                <a href="{{ route('events.edit', $event->id) }}"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Event
                </a>
            </div>
        </div>

        <!-- Event Details -->
        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h1 class="text-3xl font-bold mb-4">{{ $event->course->course_title }}</h1>

            <p class="text-lg mb-2">
                <strong>Date:</strong> {{ $event->datefrom }} to {{ $event->dateto ?? 'N/A' }}
            </p>
            <p class="text-lg mb-4">
                <strong>Facilitator:</strong> {{ $event->facilitator->first_name }} {{ $event->facilitator->last_name }}
            </p>
        </div>

        <!-- Participants List -->
        @if($event->registrations->isNotEmpty())
        <div class="mt-8">
            <h2 class="text-2xl font-bold mb-4">Participants</h2>
            <table class="min-w-full table-auto border-collapse border border-gray-500">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border px-4 py-2">Name</th>
                        <th class="border px-4 py-2">Email</th>
                        <th class="border px-4 py-2">Status</th>
                        <th class="border px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($event->registrations as $registration)
                    <tr class="hover:bg-gray-100">
                        <td class="border px-4 py-2">
                            {{ $registration->student->first_name }} {{ $registration->student->last_name }}
                        </td>
                        <td class="border px-4 py-2">{{ $registration->student->email }}</td>
                        <td class="border px-4 py-2">{{ $registration->end_status }}</td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('registrations.show', $registration->id) }}"
                                class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-lg text-gray-600 mt-4">No participants registered yet.</p>
        @endif
    </div>
</x-layout>