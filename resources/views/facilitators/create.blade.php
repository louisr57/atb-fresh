<x-layout>
    <x-slot:heading>
        Create New Facilitator
    </x-slot:heading>

    <div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-lg mt-10">
        {{-- TODO: // convert to CSS used in show and edit blade files for consistency --}}

        <h2 class="text-2xl font-semibold mb-6 text-center">Create New Facilitator</h2>

        @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 mb-4 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('facilitators.store') }}" method="POST">
            @csrf
            <!-- First Name -->
            <div class="mb-4">
                <label for="first_name" class="block text-gray-700 font-medium mb-2">First Name</label>
                <input type="text" name="first_name" id="first_name"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- Last Name -->
            <div class="mb-4">
                <label for="last_name" class="block text-gray-700 font-medium mb-2">Last Name</label>
                <input type="text" name="last_name" id="last_name"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                <input type="email" name="email" id="email"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- Phone Number -->
            <div class="mb-4">
                <label for="phone_number" class="block text-gray-700 font-medium mb-2">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Date of Birth -->
            <div class="mb-6">
                <label for="dob" class="block text-gray-700 font-medium mb-2">Date of Birth</label>
                <input type="text" name="dob" id="dob" {{--
                    value="{{ old('dob', $facilitator->dob ? \Carbon\Carbon::parse($facilitator->dob)->format('Y-m-d') : '') }}"
                    --}}
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required>
            </div>

            <!-- Address -->
            <div class="mb-4">
                <label for="address" class="block text-gray-700 font-medium mb-2">Address</label>
                <input type="text" name="address" id="address"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- City -->
            <div class="mb-4">
                <label for="city" class="block text-gray-700 font-medium mb-2">City</label>
                <input type="text" name="city" id="city"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- State -->
            <div class="mb-4">
                <label for="state" class="block text-gray-700 font-medium mb-2">State</label>
                <input type="text" name="state" id="state"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Country -->
            <div class="mb-4">
                <label for="country" class="block text-gray-700 font-medium mb-2">Country</label>
                <input type="text" name="country" id="country"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Post Code -->
            <div class="mb-6">
                <label for="post_code" class="block text-gray-700 font-medium mb-2">Post Code</label>
                <input type="text" name="post_code" id="post_code"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Website -->
            <div class="mb-6">
                <label for="website" class="block text-gray-700 font-medium mb-2">Website</label>
                <input type="text" name="website" id="website"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Create
                    Facilitator</button>
            </div>
        </form>
    </div>

    <!-- Include Pikaday CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var currentYear = new Date().getFullYear();
            var minBirthYear = currentYear - 100; // Assuming a maximum age of 100 years
            var maxBirthYear = currentYear - 18;  // Assuming a minimum age of 18 years

            var picker = new Pikaday({
                field: document.getElementById('dob'),
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