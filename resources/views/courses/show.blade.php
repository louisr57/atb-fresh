<x-layout>

    <x-slot:heading>
        Course Details
    </x-slot:heading>

    <div class="container mx-auto p-4">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">{{ $course->course_title }}</h1>
            @if($eventsCount === 0)
            <form action="{{ route('courses.destroy', $course->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                    onclick="return confirm('Are you sure you want to delete this course?');">
                    Delete Course
                </button>
            </form>
            @endif
            <a href="{{ route('courses.edit', $course->id) }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Edit Course
            </a>
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

        @if($events->isNotEmpty())
        <h2 class="text-2xl font-bold mb-5 mt-5">Events for this Course</h2>
        <div class="relative overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2 text-left">Event Title</th>
                        <th class="border px-4 py-2 text-left">
                            <a href="{{ route('courses.show', ['id' => $course->id, 'sort_by' => 'participant_count', 'direction' => ($sort_by === 'participant_count' && $direction === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center">
                                Participant Count
                                @if($sort_by === 'participant_count')
                                    <span class="ml-1">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2 text-left">
                            <a href="{{ route('courses.show', ['id' => $course->id, 'sort_by' => 'datefrom', 'direction' => ($sort_by === 'datefrom' && $direction === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center">
                                Start Date
                                @if($sort_by === 'datefrom')
                                    <span class="ml-1">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2 text-left">End Date</th>
                        <th class="border px-4 py-2 text-left">
                            <a href="{{ route('courses.show', ['id' => $course->id, 'sort_by' => 'facilitator', 'direction' => ($sort_by === 'facilitator' && $direction === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center">
                                Facilitator
                                @if($sort_by === 'facilitator')
                                    <span class="ml-1">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2 text-left">
                            <a href="{{ route('courses.show', ['id' => $course->id, 'sort_by' => 'venue', 'direction' => ($sort_by === 'venue' && $direction === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center">
                                Venue
                                @if($sort_by === 'venue')
                                    <span class="ml-1">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2 text-left">
                            <a href="{{ route('courses.show', ['id' => $course->id, 'sort_by' => 'city', 'direction' => ($sort_by === 'city' && $direction === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center">
                                City
                                @if($sort_by === 'city')
                                    <span class="ml-1">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2 text-left">
                            <a href="{{ route('courses.show', ['id' => $course->id, 'sort_by' => 'state', 'direction' => ($sort_by === 'state' && $direction === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center">
                                State
                                @if($sort_by === 'state')
                                    <span class="ml-1">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2 text-left">
                            <a href="{{ route('courses.show', ['id' => $course->id, 'sort_by' => 'country', 'direction' => ($sort_by === 'country' && $direction === 'asc') ? 'desc' : 'asc']) }}" class="flex items-center">
                                Country
                                @if($sort_by === 'country')
                                    <span class="ml-1">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                    <tr class="hover:bg-gray-100 cursor-pointer" onclick="window.location='{{ route('events.show', $event->id) }}'">
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->title }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->participant_count }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->datefrom }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->dateto }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            @forelse ($event->facilitators as $facilitator)
                                {{ $facilitator->first_name }} {{ $facilitator->last_name }}
                                @unless ($loop->last), @endunless
                            @empty
                                No facilitator assigned
                            @endforelse
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->venue->venue_name ?? 'N/A' }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->venue->city ?? 'N/A' }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->venue->state ?? 'N/A' }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->venue->country ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-lg text-gray-600">No events have been scheduled for this course yet.</p>
        @endif

        {{-- Pagination Links --}}
        <div class="mt-4">
            {{ $events->appends(['sort_by' => $sort_by, 'direction' => $direction])->links() }}
        </div>

        <div class="mt-6">
            <a href="{{ route('courses.index') }}" class="text-blue-600 hover:underline">← Back to Courses List</a>
        </div>
    </div>
</x-layout>
