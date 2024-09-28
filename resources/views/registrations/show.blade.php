<x-layout>

    <x-slot:heading>
        Registration Details
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Registration Details</h1>

        <table class="min-w-full table-auto border-collapse border border-gray-300 mb-4">
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Student Name</th>
                <td class="border px-4 py-2">
                    {{ $registration->student->first_name }} {{ $registration->student->last_name }}
                </td>
            </tr>
            <tr>
                <th class="border px-4 py-2">Course Name</th>
                <td class="border px-4 py-2">{{ $registration->event->course->course_title }}</td>
            </tr>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Course Start Date</th>
                <td class="border px-4 py-2">{{ $registration->event->datefrom }}</td>
            </tr>
            <tr>
                <th class="border px-4 py-2">Course End Date</th>
                <td class="border px-4 py-2">{{ $registration->event->dateto ?? 'N/A' }}</td>
            </tr>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Instructor Name</th>
                <td class="border px-4 py-2">
                    {{ $registration->event->instructor->first_name }} {{
                    $registration->event->instructor->last_name }}
                </td>
            </tr>
            <tr>
                <th class="border px-4 py-2">Registration Status</th>
                <td class="border px-4 py-2">{{ ucfirst($registration->end_status) }}</td>
            </tr>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Remarks</th>
                <td class="border px-4 py-2">{{ $registration->comments ?? 'No remarks' }}</td>
            </tr>
        </table>

        <a href="{{ route('registrations.index') }}" class="text-blue-600 hover:underline">Back to Registrations
            List</a>
    </div>


</x-layout>