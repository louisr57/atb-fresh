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
            <div class="sticky bottom-52 overflow-x-auto">
                <table class="min-w-full table-auto border-collapse border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <!-- "Debug info" Column -->
                            {{-- <th class="sticky left-0 bg-gray-100 border px-4 py-2">
                                Debug Info
                            </th> --}}
                            <!-- Sticky "Action" Column -->
                            <th class="bg-gray-100 border px-4 py-2">
                                Action
                            </th>
                            <th class="border px-4 py-2">
                                <a
                                    href="{{ route('registrations.index', ['sort_by' => 'student_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                    Student Name
                                    @if ($sort_by === 'student_name')
                                    @if(request('direction') == 'asc')
                                    <i class="fa fa-sort-asc"></i>
                                    @else
                                    <i class="fa fa-sort-desc"></i>
                                    @endif
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
                                    href="{{ route('registrations.index', ['sort_by' => 'id', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                    Sequence
                                    @if ($sort_by === 'id')
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
                                    href="{{ route('registrations.index', ['sort_by' => 'facilitator_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
                                    facilitator Name
                                    @if ($sort_by === 'facilitator_name')
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
                        <tr class="hover:bg-gray-100 registration-row">
                            <!-- Debug Information -->
                            {{-- <td>
                                Registration ID: {{ $registration->id }} <br>
                                Student ID: {{ $registration->student->id }} <br>
                                Student Name: {{ $registration->student->first_name }} {{
                                $registration->student->last_name
                                }}<br>
                                Course ID: {{ $registration->event->course->id }} <br>
                                Course Name: {{ $registration->event->course->course_title }} <br>
                                facilitator ID: {{ $registration->event->facilitator->id }} <br>
                                facilitator Name: {{ $registration->event->facilitator->first_name }} {{
                                $registration->event->facilitator->last_name }} <br>
                            </td> --}}
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
                            <!-- Safeguard for null event or course -->
                            <td class="border px-4 py-2 whitespace-nowrap">
                                {{ $registration->event->course->course_title ?? 'N/A' }}
                            </td>
                            <td class="border px-4 py-2 whitespace-nowrap">
                                {{ $registration->event->course->id ?? 'N/A' }}
                            </td>
                            <td class="border px-4 py-2 whitespace-nowrap">
                                {{ $registration->event->datefrom ?? 'N/A' }}
                            </td>
                            <td class="border px-4 py-2 whitespace-nowrap">
                                {{ $registration->event->dateto ?? 'N/A' }}
                            </td>
                            <td class="border px-4 py-2 whitespace-nowrap">
                                {{ $registration->event->facilitator->first_name ?? 'N/A' }} {{
                                $registration->event->facilitator->last_name ?? 'N/A' }}
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
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $registrations->appends(request()->input())->links() }}
    </div>
    </div>

    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Make horizontal scroll sticky at the bottom
            const table = document.getElementById('registrations-table');
            table.parentElement.addEventListener('scroll', function() {
                table.parentElement.scrollTop = 0; // Keep scroll locked at the top
            });
        });
    </script> --}}

</x-layout>