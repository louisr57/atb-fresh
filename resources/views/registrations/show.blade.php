<x-layout>
    <x-slot:heading>
        Registrations Details
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Registration Details</h1>
            @php
                $position = $registration->getPositionInEvent();
            @endphp
            <span class="text-gray-600 text-xl mr-3 font-semibold">
                {{ $position['current'] }} of {{ $position['total'] }}
            </span>
        </div>

        <table class="min-w-full table-fixed border-collapse border border-gray-300 mb-4">
            <tr class="bg-gray-100">
                <th class="w-1/4 border px-4 py-2 text-left">Student ID</th>
                <td class="border px-4 py-2">
                    {{ $registration->student->id }}
                </td>
            </tr>
            <tr class="bg-gray-100">
                <th class="w-1/4 border px-4 py-2 text-left">Registration ID</th>
                <td class="border px-4 py-2">
                    {{ $registration->id }}
                </td>
            </tr>
            <tr class="bg-gray-100">
                <th class="w-1/4 border px-4 py-2 text-left">Student Name</th>
                <td class="border px-4 py-2">
                    {{ $registration->student->first_name }} {{ $registration->student->last_name }}
                </td>
            </tr>
            <tr>
                <th class="w-1/4 border px-4 py-2 text-left">Course Name</th>
                <td class="border px-4 py-2">{{ $registration->event->course->course_title }}</td>
            </tr>
            <tr class="bg-gray-100">
                <th class="w-1/4 border px-4 py-2 text-left">Course Start Date</th>
                <td class="border px-4 py-2">{{ $registration->event->datefrom }}</td>
            </tr>
            <tr>
                <th class="w-1/4 border px-4 py-2 text-left">Course End Date</th>
                <td class="border px-4 py-2">{{ $registration->event->dateto ?? 'N/A' }}</td>
            </tr>
            <tr class="bg-gray-100">
                <th class="w-1/4 border px-4 py-2 text-left">
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
            <a href="{{ route('events.show', $registration->event) }}" class="text-blue-600 text-xl font-bold hover:underline">Back to Events List</a>
        </div>
    </div>

    @stack('scripts')
</x-layout>
