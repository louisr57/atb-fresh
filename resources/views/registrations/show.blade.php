<x-layout>
    <x-slot:heading>
        Registrations Details
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Registration Details</h1>

        <table class="min-w-full table-auto border-collapse border border-gray-300 mb-4">
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Student ID</th>
                <td class="border px-4 py-2">
                    {{ $registration->student->id }}
                </td>
            </tr>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Registration ID</th>
                <td class="border px-4 py-2">
                    {{ $registration->id }}
                </td>
            </tr>
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
                <th class="border px-4 py-2">
                    Facilitator(s)</th>
                <td class="border px-4 py-2">
                    @forelse ($registration->event->facilitators as $facilitator)
                        {{ $facilitator->first_name }} {{ $facilitator->last_name }}
                        @unless ($loop->last), @endunless
                    @empty
                        No facilitator assigned
                    @endforelse
                </td>
            </tr>
            @include('registrations._details', ['registration' => $registration])
        </table>

        <div class="mt-4">
            <a href="{{ route('events.show', $registration->event) }}" class="text-blue-600 hover:underline">Back to Events List</a>
        </div>
    </div>

    @stack('scripts')
</x-layout>
