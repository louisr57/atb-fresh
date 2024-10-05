<x-layout>
    <x-slot:heading>
        Create New Student
    </x-slot:heading>

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

                <!-- Address -->
                <div class="mb-4">
                    <label for="address" class="block text-gray-700 font-bold mb-2">Address</label>
                    <input type="text" id="address" name="address"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                        value="{{ old('address') }}" required>
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
                            <option value="{{ $country['iso_3166_1_alpha2'] }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>

                        {{-- <select name="country" id="country"
                            class="form-control shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200 value="
                            {{ old('country') }}" required">
                            <option value="">Loading countries...</option>
                        </select> --}}

                        {{-- <input type="text" id="country" name="country"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('country') }}" required> --}}
                    </div>

                    <!-- Post Code -->
                    <div class="mb-4">
                        <label for="post_code" class="block text-gray-700 font-bold mb-2">Post Code</label>
                        <input type="text" id="post_code" name="post_code"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('post_code') }}" required>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- <script>
        // Fetch the countries from the API
        async function fetchCountries() {
            const response = await fetch('https://restcountries.com/v3.1/all');
            const countries = await response.json();

            const countrySelect = document.getElementById('country');
            countrySelect.innerHTML = ''; // Clear existing options

            // Populate the dropdown with country options
            countries.forEach(country => {
                const option = document.createElement('option');
                option.value = country.cca2; // ISO 3166-1 alpha-2 country code
                option.textContent = country.name.common; // Country name
                countrySelect.appendChild(option);
            });

            // Add a placeholder option
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = 'Select a country';
            placeholderOption.selected = true;
            placeholderOption.disabled = true;
            countrySelect.prepend(placeholderOption);
        }

        // Call the function when the page loads
        document.addEventListener('DOMContentLoaded', fetchCountries);
    </script> --}}
</x-layout>