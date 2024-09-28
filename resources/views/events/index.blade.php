@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Calendar Events
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Events (Calendar) List</h1>

        <!-- Wrapping the table inside a div to make it horizontally scrollable -->
        <div class="relative overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <!-- Action column -->
                        <th class="border px-4 py-2 sticky left-0 bg-gray-100 z-10">
                            Action
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('events.index', ['sort_by' => 'course_title', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                Course Title
                                @if ($sort_by === 'course_title')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('events.index', ['sort_by' => 'participant_count', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                Participant Count
                                @if ($sort_by === 'participant_count')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('events.index', ['sort_by' => 'datefrom', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                Start Date
                                @if ($sort_by === 'datefrom')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('events.index', ['sort_by' => 'dateto', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                End Date
                                @if ($sort_by === 'dateto')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">Venue</th>
                        <th class="border px-4 py-2">City</th>
                        <th class="border px-4 py-2">State</th>
                        <th class="border px-4 py-2">Country</th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('events.index', ['sort_by' => 'instructor_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                Instructor Name
                                @if ($sort_by === 'instructor_name')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($events as $event)
                    <tr class="hover:bg-gray-100">
                        <!-- Action column with sticky left positioning -->
                        <td class="border px-4 py-2 sticky left-0 bg-white z-10">
                            <a href="{{ route('events.show', $event->id) }}" class="text-blue-600 hover:underline">
                                View
                            </a>
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $event->course->course_title }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $event->participant_count }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $event->datefrom }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $event->dateto ?? 'N/A' }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->venue }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->city }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->state }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->country }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $event->instructor->first_name }} {{ $event->instructor->last_name }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $event->remarks ?? 'No remarks' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $events->appends(request()->input())->links() }}
        </div>
    </div>

</x-layout>