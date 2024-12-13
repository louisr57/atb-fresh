<x-layout>

    <x-slot:heading>
        Student Details
    </x-slot:heading>

    <div class="container mx-auto p-4">

        <!-- Flash Message Section -->
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            {{ session('success') }}
        </div>
        @endif

        <!-- Flex container for student name and delete button -->
        <div class="flex justify-between items-center mb-4">

            <!-- student Name -->
            <h1 class="text-3xl font-bold mb-4">{{ $student->first_name }} {{ $student->last_name }}</h1>

            <!-- Delete Button ... will only show if there are no events for this student -->
            @if($student->registrations->isEmpty())
            <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="top-0 right-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700"
                    onclick="return confirm('Are you sure you want to delete this student?');">
                    Delete Student
                </button>
            </form>
            @endif

            <a href="{{ route('students.edit', $student->id) }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                Edit Student
            </a>
        </div>

        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- First Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">First Name</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->first_name }}
                    </p>
                </div>

                <!-- Last Name -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Last Name</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->last_name }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Email -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Email</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->email }}
                    </p>
                </div>

                <!-- Phone Number -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Phone Number</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->phone_number ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Date of Birth -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Date of Birth</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ \Carbon\Carbon::parse($student->dob)->format('Y-m-d') ?? 'N/A' }}
                    </p>
                </div>

                <!-- Address -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Address</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->address ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- City -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">City</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->city ?? 'N/A' }}
                    </p>
                </div>

                <!-- State -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">State</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->state ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Country -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Country</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->country ?? 'N/A' }}
                    </p>
                </div>

                <!-- Post Code -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Post Code</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->post_code ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Website -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Website</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->website ?? 'N/A' }}
                    </p>
                </div>

                <!-- Extra ID ... like passport number, driver's licence, social insurance number, etc. -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Extra ID</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->ident ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Next of kin ... who to contact in case of emergencies -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Next of Kin</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->next_of_kin ?? 'N/A' }}
                    </p>
                </div>

                <!-- Allergies -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Allergies</label>
                    <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                        {{ $student->allergies ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <!-- Special Needs -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Special Needs</label>
                <p class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200">
                    {{ $student->special_needs ?? 'N/A' }}
                </p>
            </div>

        </div>

        @if($student->registrations->isNotEmpty())
        <div class="mb-6 mt-10">
            <h2 class="text-2xl text-blue-700 font-semibold">Past and Future Courses</h2></br>

            <table class="min-w-full table-auto border-collapse border border-gray-500">
                <thead class="bg-gray-200 border border-gray-500 px-4 py-2 text-left">
                    <tr>
                        <th class="border border-gray-500 px-4 py-2">Course Title</th>
                        <th class="border border-gray-500 px-4 py-2">Start Date</th>
                        <th class="border border-gray-500 px-4 py-2">End Date</th>
                        <th class="border border-gray-500 px-4 py-2">Facilitator</th>
                        <th class="border border-gray-500 px-4 py-2">Registration Status</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-50">
                    @foreach($student->registrations as $registration)
                    <tr onclick="window.location='{{ route('events.show', $registration->event->id) }}'" class="hover:bg-sky-100 border-gray-500 registration-row cursor-pointer">
                        <td class="border border-gray-500 px-4 py-2">{{ $registration->event->course->course_title }}</td>
                        <td class="border border-gray-500 px-4 py-2">{{ $registration->event->datefrom }}</td>
                        <td class="border border-gray-500 px-4 py-2">{{ $registration->event->dateto }}</td>
                        <td class="border border-gray-500 px-4 py-2">
                            {{ $registration->event->facilitator->first_name }} {{
                            $registration->event->facilitator->last_name }}
                        </td>
                        <td class="border border-gray-500 px-4 py-2">{{ $registration->end_status }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @else
            <p>No registrations found for this student.</p>
            @endif

            <!-- Back to students list -->
            <div class="mt-4">
                <a href="{{ route('students.index') }}" class="text-blue-600 hover:underline">
                    ← Back to Students List
                </a>
            </div>
        </div>
    </div>

</x-layout>
