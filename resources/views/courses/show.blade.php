<x-layout>

    <x-slot:heading>
        Course Details
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">{{ $course->course_title }}</h1>

        <div class="mb-6">
            <h2 class="text-xl font-semibold">Course Description</h2>
            <p class="text-gray-700">{{ $course->description }}</p>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-semibold">Duration</h2>
            <p class="text-gray-700">{{ $course->duration }} days</p>
        </div>

        @if($course->events->isNotEmpty())
        <div class="mb-6">
            <h2 class="text-xl font-semibold">Past and Upcoming Events</h2>

            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2">Event Title</th>
                        <th class="border px-4 py-2">Start Date</th>
                        <th class="border px-4 py-2">End Date</th>
                        <th class="border px-4 py-2">Instructor</th>
                        <th class="border px-4 py-2">Venue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($course->events as $event)
                    <tr class="hover:bg-gray-100">
                        <td class="border px-4 py-2">{{ $event->title }}</td>
                        <td class="border px-4 py-2">{{ $event->datefrom }}</td>
                        <td class="border px-4 py-2">{{ $event->dateto }}</td>
                        <td class="border px-4 py-2">
                            {{ $event->instructor->first_name }} {{ $event->instructor->last_name }}
                        </td>
                        <td class="border px-4 py-2">{{ $event->venue }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p>No events scheduled for this course.</p>
        @endif

        <!-- Back to courses list -->
        <div class="mt-4">
            <a href="{{ route('courses.index') }}" class="text-blue-600 hover:underline">
                ← Back to Courses List
            </a>
        </div>
    </div>

</x-layout>