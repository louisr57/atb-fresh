@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Course Registrations
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Registrations List</h1>

        <!-- Wrapping the table inside a div to make it horizontally scrollable -->
        <div class="relative overflow-x-auto">
            <!-- Sticky horizontal scroll bar wrapper -->
            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <!-- Sticky "Action" Column -->
                        <th class="sticky left-0 bg-gray-100 border px-4 py-2">
                            Action
                        </th>
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
                        <!-- Sticky "View" Button -->
                        <td class="sticky left-0 bg-gray-100 border px-4 py-2">
                            <a href="{{ route('registrations.show', $registration->id) }}"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">
                                View
                            </a>
                        </td>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Make horizontal scroll sticky at the bottom
            const table = document.getElementById('registrations-table');
            table.parentElement.addEventListener('scroll', function() {
                table.parentElement.scrollTop = 0; // Keep scroll locked at the top
            });
        });
    </script>

</x-layout>