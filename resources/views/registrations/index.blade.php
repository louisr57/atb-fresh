@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Course Registrations
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Registrations List</h1>

        <!-- Wrapping the table inside a div to make it horizontally scrollable -->
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2">Actions</th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('registrations.index', ['sort_by' => 'student_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                Student Name
                                @if ($sort_by === 'student_name')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('registrations.index', ['sort_by' => 'course_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                Course Name
                                @if ($sort_by === 'course_name')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('registrations.index', ['sort_by' => 'datefrom', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                Start Date
                                @if ($sort_by === 'datefrom')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('registrations.index', ['sort_by' => 'dateto', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                End Date
                                @if ($sort_by === 'dateto')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('registrations.index', ['sort_by' => 'instructor_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                Instructor Name
                                @if ($sort_by === 'instructor_name')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('registrations.index', ['sort_by' => 'end_status', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                Status
                                @if ($sort_by === 'end_status')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($registrations as $registration)
                    <tr class="hover:bg-gray-100">
                        {{-- <td class="border px-4 py-2 whitespace-nowrap">
                            <a href="{{ route('registrations.show', $registration->id) }}"
                                class="text-blue-600 hover:underline">
                                View
                            </a>
                        </td> --}}
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $registration->student->first_name }} {{ $registration->student->last_name }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $registration->event->course->course_title }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $registration->event->datefrom }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $registration->event->dateto ?? 'N/A' }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $registration->event->instructor->first_name }} {{
                            $registration->event->instructor->last_name }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ ucfirst($registration->end_status) }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $registration->comments ?? 'No remarks' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $registrations->appends(request()->input())->links() }}
        </div>
    </div>


</x-layout>