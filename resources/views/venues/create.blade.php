<x-layout>
    <x-slot:heading>
        Create New Venue
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Create New Venue</h1>

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
            <form action="{{ route('venues.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Venue Name -->
                    <div class="mb-4">
                        <label for="venue_name" class="block text-gray-700 font-bold mb-2">Venue Name</label>
                        <input type="text" id="venue_name" name="venue_name"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('venue_name') }}" required>
                    </div>

                    <!-- Address -->
                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 font-bold mb-2">Address</label>
                        <input type="text" id="address" name="address"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('address') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

                    <!-- Post Code -->
                    <div class="mb-4">
                        <label for="postcode" class="block text-gray-700 font-bold mb-2">Post Code</label>
                        <input type="text" id="postcode" name="postcode"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('postcode') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Country -->
                    <div class="mb-4">
                        <label for="country" class="block text-gray-700 font-bold mb-2">Country</label>
                        <select name="country" id="country"
                            class="form-control shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            required>
                            <option value="">Select a country</option>
                            @foreach($countries as $country)
                            <option value="{{ $country['name'] }}" {{ old('country')==$country['name'] ? 'selected' : ''
                                }}>
                                {{ $country['name'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Location Geocode -->
                    <div class="mb-4">
                        <label for="location_geocode" class="block text-gray-700 font-bold mb-2">Location
                            Geocode</label>
                        <input type="text" id="location_geocode" name="location_geocode"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('location_geocode') }}">
                    </div>
                </div>

                <!-- Remarks -->
                <div class="mb-4">
                    <label for="remarks" class="block text-gray-700 font-bold mb-2">Remarks</label>
                    <textarea id="remarks" name="remarks"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200 h-24">{{ old('remarks') }}</textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Create Venue
                    </button>
                    <a href="{{ route('venues.index') }}" class="text-blue-600 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layout>