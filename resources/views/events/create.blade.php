<x-layout>
    <x-slot:heading>
        Create Events
    </x-slot:heading>

    @section('content')
    <div class="max-w-4xl mx-auto mt-10">
        <h1 class="text-2xl font-bold mb-5">Create New Event</h1>

        @if(session('success'))
        <div class="bg-green-500 text-white p-4 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('events.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="title" class="block text-gray-700">Event Title:</label>
                <input type="text" id="title" name="title" class="w-full p-2 border rounded" required>
            </div>

            <div class="mb-4">
                <label for="datefrom" class="block text-gray-700 font-medium mb-2">Start Date</label>
                <input type="text" name="datefrom" id="datepicker"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required>
            </div>

            <div class="mb-4">
                <label for="dateto" class="block text-gray-700 font-medium mb-2">End Date</label>
                <input type="text" name="dateto" id="datepicker"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required>
            </div>

            {{-- <div class="mb-4">
                <label for="datefrom" class="block text-gray-700">Start Date:</label>
                <input type="date" id="datefrom" name="datefrom" class="w-full p-2 border rounded" required>
            </div> --}}

            <!-- Course Selection Modal -->
            <div class="mb-4">
                <label for="course_id" class="block text-gray-700">Select Course:</label>
                <button type="button" id="courseModalBtn" class="bg-blue-500 text-white px-4 py-2 rounded">Select
                    Course</button>
                <input type="hidden" id="course_id" name="course_id" required>
                <p id="selectedCourse" class="mt-2 text-gray-500"></p>
            </div>

            <!-- Facilitator Selection Modal -->
            <div class="mb-4">
                <label for="facilitator_id" class="block text-gray-700">Select Facilitator:</label>
                <button type="button" id="facilitatorModalBtn" class="bg-blue-500 text-white px-4 py-2 rounded">Select
                    Facilitator</button>
                <input type="hidden" id="facilitator_id" name="facilitator_id" required>
                <p id="selectedFacilitator" class="mt-2 text-gray-500"></p>
            </div>

            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Create Event</button>
        </form>
    </div>

    <!-- Course Modal -->
    <div id="courseModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 z-50">
        <div class="bg-white rounded-lg p-6 max-w-lg w-full">
            <h2 class="text-xl mb-4">Select a Course</h2>
            <ul>
                @foreach($courses as $course)
                <li class="cursor-pointer hover:bg-gray-200 p-2"
                    onclick="selectCourse('{{ $course->id }}', '{{ $course->course_title }}')">
                    {{ $course->course_title }}
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Facilitator Modal -->
    <div id="facilitatorModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 z-50">
        <div class="bg-white rounded-lg p-6 max-w-lg w-full">
            <h2 class="text-xl mb-4">Select a Facilitator</h2>
            <ul>
                @foreach($facilitators as $facilitator)
                <li class="cursor-pointer hover:bg-gray-200 p-2"
                    onclick="selectFacilitator('{{ $facilitator->id }}', '{{ $facilitator->first_name }} {{ $facilitator->last_name }}')">
                    {{ $facilitator->first_name }} {{ $facilitator->last_name }}
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    <script>
        // Show the course modal
        document.getElementById('courseModalBtn').onclick = function() {
            document.getElementById('courseModal').classList.remove('hidden');
        };

        // Select a course and close the modal
        function selectCourse(id, title) {
            document.getElementById('course_id').value = id;
            document.getElementById('selectedCourse').textContent = "Selected Course: " + title;
            closeCourseModal();
        }

        // Close the course modal
        function closeCourseModal() {
            document.getElementById('courseModal').classList.add('hidden');
        }

        // Show the facilitator modal
        document.getElementById('facilitatorModalBtn').onclick = function() {
            document.getElementById('facilitatorModal').classList.remove('hidden');
        };

        // Select a facilitator and close the modal
        function selectFacilitator(id, name) {
            document.getElementById('facilitator_id').value = id;
            document.getElementById('selectedFacilitator').textContent = "Selected Facilitator: " + name;
            closeFacilitatorModal();
        }

        // Close the facilitator modal
        function closeFacilitatorModal() {
            document.getElementById('facilitatorModal').classList.add('hidden');
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        var currentYear = new Date().getFullYear();
        var minBirthYear = currentYear - 100; // Assuming a maximum age of 100 years
        var maxBirthYear = currentYear - 18;  // Assuming a minimum age of 18 years

        var picker = new Pikaday({
            field: document.getElementById('datepicker'),
            format: 'YYYY-MM-DD',yearRange: [minBirthYear, maxBirthYear],
            yearRange: [minBirthYear, maxBirthYear],
            maxDate: new Date(maxBirthYear, 11, 31),
            minDate: new Date(minBirthYear, 0, 1),
            defaultDate: new Date(maxBirthYear, 0, 1),
            showYearDropdown: true,
            showMonthDropdown: true,
            // Allow the field to be cleared
            setDefaultDate: false,
            toString(date, format) {
                date.setHours(12, 0, 0, 0);
                return date ? date.toISOString().split('T')[0] : '';
            },
            parse(dateString, format) {
                const parts = dateString.split('-');
                return dateString ? new Date(parts[0], parts[1] - 1, parts[2], 12, 0, 0) : null;
            },
            onSelect: function(date) {
                date.setHours(12, 0, 0, 0);
                this._field.value = date.toISOString().split('T')[0];
            },
        });
    });
    </script>

</x-layout>