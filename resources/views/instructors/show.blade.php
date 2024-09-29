<x-layout>

    <x-slot:heading>
        Instructor Details
    </x-slot:heading>


    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">{{ $instructor->first_name }} {{ $instructor->last_name }}</h1>

        <div class="mb-6">
            <h2 class="text-xl font-semibold">Email</h2>
            <p class="text-gray-700">{{ $instructor->email }}</p>
        </div>

        @if($instructor->events->isNotEmpty())
        <div class="mb-6">
            <h2 class="text-xl font-semibold">Courses Taught</h2>

            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2">Course Title</th>
                        <th class="border px-4 py-2">Event Title</th>
                        <th class="border px-4 py-2">Start Date</th>
                        <th class="border px-4 py-2">End Date</th>
                        <th class="border px-4 py-2">Venue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($instructor->events as $event)
                    <tr class="hover:bg-gray-100">
                        <td class="border px-4 py-2">{{ $event->course->course_title }}</td>
                        <td class="border px-4 py-2">{{ $event->title }}</td>
                        <td class="border px-4 py-2">{{ $event->datefrom }}</td>
                        <td class="border px-4 py-2">{{ $event->dateto }}</td>
                        <td class="border px-4 py-2">{{ $event->venue }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p>This instructor has not taught any courses yet.</p>
        @endif

        <!-- Back to instructors list -->
        <div class="mt-4">
            <a href="{{ route('instructors.index') }}" class="text-blue-600 hover:underline">
                ← Back to Instructors List
            </a>
        </div>
    </div>

</x-layout>