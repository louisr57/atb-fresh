<x-layout>
    <x-slot:heading>
        Create New Student
    </x-slot:heading>

    {{-- TODO: Need to add the missing fields including dob javascript --}}

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Create New Student</h1>

        <!-- Display validation errors, if any -->
        @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 mb-4 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <form action="{{ route('students.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- First Name -->
                    <div class="mb-4">
                        <label for="first_name" class="block text-gray-700 font-bold mb-2">First Name</label>
                        <input type="text" id="first_name" name="first_name"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('first_name') }}" required>
                    </div>

                    <!-- Last Name -->
                    <div class="mb-4">
                        <label for="last_name" class="block text-gray-700 font-bold mb-2">Last Name</label>
                        <input type="text" id="last_name" name="last_name"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                        <input type="email" id="email" name="email"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('email') }}" required>
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-4">
                        <label for="phone_number" class="block text-gray-700 font-bold mb-2">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('phone_number') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-6">
                        <label for="dob" class="block text-gray-700 font-bold mb-2">Date of Birth</label>
                        <input type="text" name="dob" id="dob"
                            value="{{ old('dob') }}"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200 focus:outline-none focus:shadow-outline"
                            required readonly autocomplete="off">
                    </div>

                    <!-- Address -->
                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 font-bold mb-2">Address</label>
                        <input type="text" id="address" name="address"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('address') }}" required>
                    </div>

                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- City -->
                    <div class="mb-4">
                        <label for="city" class="block text-gray-700 font-bold mb-2">City</label>
                        <input type="text" id="city" name="city"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('city') }}" required>
                    </div>

                    <!-- State -->
                    <div class="mb-4">
                        <label for="state" class="block text-gray-700 font-bold mb-2">State</label>
                        <input type="text" id="state" name="state"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('state') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Country -->
                    <div class="mb-4">
                        <label for="country" class="block text-gray-700 font-bold mb-2">Country</label>
                        <select name="country" id="country"
                            class="form-control shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('country') }} required">
                            <option value="">Select a country</option>
                            @foreach($countries as $country)
                            <option value="{{ $country['name'] }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Post Code -->
                    <div class="mb-4">
                        <label for="post_code" class="block text-gray-700 font-bold mb-2">Post Code</label>
                        <input type="text" id="post_code" name="post_code"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('post_code') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Website -->
                    <div class="mb-4">
                        <label for="website" class="block text-gray-700 font-bold mb-2">Website</label>
                        <input type="text" id="website" name="website"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('website') }}">
                    </div>

                    <!-- Extra ID ... like passport number, driver's licence, social insurance number, etc. -->
                    <div class="mb-4">
                        <label for="ident" class="block text-gray-700 font-bold mb-2">Extra ID</label>
                        <input type="text" id="ident" name="ident"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('ident') }}">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Next of kin ... who to contact in case of emergencies -->
                    <div class="mb-4">
                        <label for="next_of_kin" class="block text-gray-700 font-bold mb-2">Next of Kin</label>
                        <input type="text" id="next_of_kin" name="next_of_kin"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('next_of_kin') }}">
                    </div>

                    <!-- Allergies -->
                    <div class="mb-4">
                        <label for="allergies" class="block text-gray-700 font-bold mb-2">Allergies</label>
                        <input type="text" id="allergies" name="allergies"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('allergies') }}">
                    </div>
                </div>

                <!-- Special Needs -->
                <div class="mb-4">
                    <label for="special_needs" class="block text-gray-700 font-bold mb-2">Special Needs</label>
                    <input type="text" id="special_needs" name="special_needs"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                        value="{{ old('special_needs') }}">
                </div>

                <!-- Submit Button -->
                <div class="flex justify-between items-center mt-6">
                    <button type="submit"
                        class="mt-7 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Submit
                    </button>
                    <a href="{{ route('students.index') }}" class="mt-7 bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
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
