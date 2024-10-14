<x-layout>

    <x-slot:heading>
        Course Details
    </x-slot:heading>

    <div class="container mx-auto p-4">


        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">{{ $course->course_title }}</h1>
            <a href="{{ route('courses.edit', $course->id) }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Edit Course
            </a>
            @if($course->events->isEmpty())
            <form action="{{ route('courses.destroy', $course->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                    onclick="return confirm('Are you sure you want to delete this course?');">
                    Delete Course
                </button>
            </form>
            @endif
        </div>

        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Course Code -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Course Code</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $course->course_code }}
                    </p>
                </div>

                <!-- Duration -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Duration (days)</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $course->duration }}
                    </p>
                </div>

                <!-- Prerequisites -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Prerequisites</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $course->prerequisites }}
                    </p>
                </div>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">

                <!-- Description -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Description</label>
                    <p
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200 h-24 overflow-y-auto">
                        {{ $course->description }}
                    </p>
                </div>
            </div>
        </div>

        @if($course->events->isNotEmpty())
        <h2 class="text-2xl font-bold mb-5 mt-5">Events for this Course</h2>
        <table class="min-w-full table-auto border-collapse border border-gray-500">
            <thead class="bg-gray-200 border border-gray-500 px-4 py-2 text-left">
                <tr>
                    <th class="border border-gray-500 px-4 py-2">Event Title</th>
                    <th class="border border-gray-500 px-4 py-2">Start Date</th>
                    <th class="border border-gray-500 px-4 py-2">End Date</th>
                    <th class="border border-gray-500 px-4 py-2">Facilitator</th>
                    <th class="border border-gray-500 px-4 py-2">Venue</th>
                </tr>
            </thead>
            <tbody class="bg-gray-50">
                @foreach($course->events as $event)
                <tr class="hover:bg-sky-100 border-gray-500 ">
                    <td class="border border-gray-500 px-4 py-2">{{ $event->title }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $event->datefrom }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $event->dateto }}</td>
                    <td class="border border-gray-500 px-4 py-2">
                        {{ $event->facilitator->first_name }} {{ $event->facilitator->last_name }}
                    </td>
                    <td class="border border-gray-500 px-4 py-2">{{ $event->venue }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-lg text-gray-600">No events have been scheduled for this course yet.</p>
        @endif

        <div class="mt-6">
            <a href="{{ route('courses.index') }}" class="text-blue-600 hover:underline">← Back to Courses List</a>
        </div>
    </div>
</x-layout>