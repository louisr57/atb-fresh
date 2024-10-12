<x-layout>

    <x-slot:heading>
        Facilitator Details
    </x-slot:heading>


    <div class="container mx-auto p-4">
        <!-- Flex container for title and delete button -->
        <div class="flex justify-between items-center mb-4">

            <!-- Facilitator Name -->
            <h1 class="text-3xl font-bold mb-4">{{ $facilitator->first_name }} {{ $facilitator->last_name }}</h1>

            <!-- Delete Button ... will only show if there are no events for this facilitator -->
            @if($facilitator->events->isEmpty())
            <form action="{{ route('facilitators.destroy', $facilitator->id) }}" method="POST" class="top-0 right-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700"
                    onclick="return confirm('Are you sure you want to delete this facilitator?');">
                    Delete Facilitator
                </button>
            </form>
            @endif

            <a href="{{ route('facilitators.edit', $facilitator->id) }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                Edit Facilitator
            </a>

        </div>

        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- First Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">First Name</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $facilitator->first_name }}
                    </p>
                </div>

                <!-- Last Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Last Name</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $facilitator->last_name }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Email -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Email</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $facilitator->email }}
                    </p>
                </div>

                <!-- Phone Number -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Phone Number</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $facilitator->phone_number ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Date of Birth -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Date of Birth</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        @if($facilitator->dob)
                        {{ \Carbon\Carbon::parse($facilitator->dob)->format('Y-m-d') }}
                        @else
                        N/A
                        @endif
                    </p>
                </div>

                <!-- Address -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Address</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $facilitator->address ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- City -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">City</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $facilitator->city ?? 'N/A' }}
                    </p>
                </div>

                <!-- State -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">State</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $facilitator->state ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Country -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Country</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $facilitator->country ?? 'N/A' }}
                    </p>
                </div>

                <!-- Post Code -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Post Code</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $facilitator->post_code ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Website -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Website</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $facilitator->website ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        @if($facilitator->events->isNotEmpty())
        <div class="mb-6 mt-10">
            <h2 class="text-2xl text-blue-700 font-semibold">Past and Future Courses</h2></br>

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
                    @foreach($facilitator->events as $event)
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
        <p>This Facilitator has not taught any courses yet.</p>
        @endif

        <!-- Back to facilitators list -->
        <div class="mt-4">
            <a href="{{ route('facilitators.index') }}" class="text-blue-600 hover:underline">
                ← Back to Facilitators List
            </a>
        </div>
    </div>

</x-layout>