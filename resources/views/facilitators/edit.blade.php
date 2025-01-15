<x-layout>
    <x-slot:heading>
        Edit Facilitator
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <!-- Flex container for title and buttons -->
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold mb-6">Edit Facilitator</h1>

            {{-- <div>
                <button type="submit" form="edit-facilitator-form"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Update Facilitator
                </button>
            </div> --}}
        </div>

        <form id="edit-facilitator-form" action="{{ route('facilitators.update', $facilitator->id) }}" method="POST"
            class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="mb-4">
                    <label for="first_name" class="block text-gray-700 font-bold mb-2">First Name</label>
                    <input type="text" name="first_name" id="first_name"
                        value="{{ old('first_name', $facilitator->first_name) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                </div>

                <div class="mb-4">
                    <label for="last_name" class="block text-gray-700 font-bold mb-2">Last Name</label>
                    <input type="text" name="last_name" id="last_name"
                        value="{{ old('last_name', $facilitator->last_name) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $facilitator->email) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required>
                </div>

                <div class="mb-4">
                    <label for="phone_number" class="block text-gray-700 font-bold mb-2">Phone Number</label>
                    <input type="text" name="phone_number" id="phone_number"
                        value="{{ old('phone_number', $facilitator->phone_number) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label for="dob" class="block text-gray-700 font-bold mb-2">Date of Birth</label>
                    <input type="text" name="dob" id="dob"
                        value="{{ old('dob', \Carbon\Carbon::parse($facilitator->dob)->format('Y-m-d')) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required readonly autocomplete="off">
                </div>

                <div class="mb-4">
                    <label for="address" class="block text-gray-700 font-bold mb-2">Address</label>
                    <input type="text" name="address" id="address" value="{{ old('address', $facilitator->address) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label for="city" class="block text-gray-700 font-bold mb-2">City</label>
                    <input type="text" name="city" id="city" value="{{ old('city', $facilitator->city) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label for="state" class="block text-gray-700 font-bold mb-2">State</label>
                    <input type="text" name="state" id="state" value="{{ old('state', $facilitator->state) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label for="country" class="block text-gray-700 font-bold mb-2">Country</label>
                    <select name="country" id="country"
                        class="form-control shadow appearance-none border rounded w-full py-2 px-3 text-gray-700  leading-tight focus:outline-none focus:shadow-outline"
                        required>
                        @foreach($countries as $country)
                        <option value="{{ $country['name'] }}" {{ old('country', $facilitator->country) ==
                            $country['name'] ? 'selected' : '' }}>
                            {{ $country['name'] }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="post_code" class="block text-gray-700 font-bold mb-2">Post Code</label>
                    <input type="text" name="post_code" id="post_code"
                        value="{{ old('post_code', $facilitator->post_code) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <!-- Website -->
                <div class="mb-4">
                    <label for="website" class="block text-gray-700 font-bold mb-2">Website</label>
                    <input type="text" name="website" id="website" value="{{ old('website', $facilitator->website) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit"
                    class="mt-10 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Update Facilitator
                </button>
                <a href="{{ route('facilitators.show', $facilitator->id) }}"
                    class="mt-10 bg-red-500 text-white font-bold py-2 px-4 rounded hover:bg-red-600">
                    Cancel
                </a>
            </div>
        </form>

        <!-- Back to facilitators list -->
        <div class="mt-5">
            <a href="{{ route('facilitators.index') }}" class="text-blue-600 hover:underline">
                ← Back to Facilitators List
            </a>
        </div>
    </div>

    <!-- Include Pikaday CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var currentYear = new Date().getFullYear();
            var minBirthYear = currentYear - 100; // Assuming a maximum age of 100 years
            var maxBirthYear = currentYear - 18; // Assuming a minimum age of 18 years

            var picker = new Pikaday({
                field: document.getElementById('dob'),
                format: 'YYYY-MM-DD',
                yearRange: [minBirthYear, maxBirthYear],
                maxDate: new Date(maxBirthYear, 11, 31),
                minDate: new Date(minBirthYear, 0, 1),
                showYearDropdown: true,
                showMonthDropdown: true,
                setDefaultDate: false,
                toString(date, format) {
                    // Set the time to noon in local time
                    date.setHours(12, 0, 0, 0);
                    return date.toISOString().split('T')[0];
                },

                parse(dateString, format) {
                    const parts = dateString.split('-');
                    // Create date at noon in local time
                    return dateString ? new Date(parts[0], parts[1] - 1, parts[2]) : null;
                },

                onSelect: function(date) {
                    // Set the time to noon in local time
                    date.setHours(12, 0, 0, 0);
                    this._field.value = date.toISOString().split('T')[0];
                },
            });
        });
    </script>
</x-layout>
