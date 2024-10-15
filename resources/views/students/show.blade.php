<x-layout>

    <x-slot:heading>
        Student Details
    </x-slot:heading>

    <!-- Flex container for student name and delete button -->
    <div class="flex justify-between items-center mb-4">

        <!-- student Name -->
        <h1 class="text-3xl font-bold mb-4">{{ $student->first_name }} {{ $student->last_name }}</h1>

        <!-- Delete Button ... will only show if there are no events for this student -->
        @if($student->events->isEmpty())
        <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="top-0 right-0">
            @csrf
            <div class="container mx-auto p-4">
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700"
                    onclick="return confirm('Are you sure you want to delete this student?');">
                    Delete Student
                </button>
        </form>
        @endif

        <a href="{{ route('students.edit', $student->id) }}"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
            Edit Student
        </a>

    </div>
    <div class="mb-6">
        <h2 class="text-xl font-semibold">Student Information</h2>
        <p><strong>Email:</strong> {{ $student->email }}</p>
        <p><strong>Phone:</strong> {{ $student->phone_number }}</p>
        <p><strong>Address:</strong> {{ $student->address }}, {{ $student->city }}, {{ $student->state }}, {{
            $student->country }}</p>
    </div>

    @if($student->registrations->isNotEmpty())
    <div class="mb-6">
        <h2 class="text-xl font-semibold">Course Registrations</h2>

        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-4 py-2">Course Title</th>
                    <th class="border px-4 py-2">Event Title</th>
                    <th class="border px-4 py-2">Start Date</th>
                    <th class="border px-4 py-2">End Date</th>
                    <th class="border px-4 py-2">Facilitator</th>
                    <th class="border px-4 py-2">Registration Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($student->registrations as $registration)
                <tr class="hover:bg-gray-100 registration-row">
                    <td class="border px-4 py-2">{{ $registration->event->course->course_title }}</td>
                    <td class="border px-4 py-2">{{ $registration->event->title }}</td>
                    <td class="border px-4 py-2">{{ $registration->event->datefrom }}</td>
                    <td class="border px-4 py-2">{{ $registration->event->dateto }}</td>
                    <td class="border px-4 py-2">
                        {{ $registration->event->facilitator->first_name }} {{
                        $registration->event->facilitator->last_name }}
                    </td>
                    <td class="border px-4 py-2">{{ $registration->end_status }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p>No registrations found for this student.</p>
    @endif

    <!-- Back to students list -->
    <div class="mt-4">
        <a href="{{ route('students.index') }}" class="text-blue-600 hover:underline">
            ← Back to Students List
        </a>
    </div>
    </div>

</x-layout>