<x-layout>
    <x-slot:heading>
        Edit Venue
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Edit Venue</h1>

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

        <x-similar-venues-warning form="venue-form" />

        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <form id="venue-form" x-data="{ online: @js((bool) old('is_virtual', $venue->is_virtual)) }" action="{{ route('venues.update', $venue->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Venue Name -->
                    <div class="mb-4">
                        <label for="venue_name" class="block text-gray-700 font-bold mb-2">Venue Name</label>
                        <input type="text" id="venue_name" name="venue_name"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('venue_name', $venue->venue_name) }}" required>
                    </div>

                    <!-- Address -->
                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 font-bold mb-2">Address <span x-show="online" style="display: none" class="font-normal text-sm">(optional for online)</span></label>
                        <input type="text" id="address" name="address"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('address', $venue->address) }}" :required="!online">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- City -->
                    <div class="mb-4">
                        <label for="city" class="block text-gray-700 font-bold mb-2">City <span x-show="online" style="display: none" class="font-normal text-sm">(optional for online)</span></label>
                        <input type="text" id="city" name="city"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('city', $venue->city) }}" :required="!online">
                    </div>

                    <!-- State -->
                    <div class="mb-4">
                        <label for="state" class="block text-gray-700 font-bold mb-2">State <span x-show="online" style="display: none" class="font-normal text-sm">(optional for online)</span></label>
                        <input type="text" id="state" name="state"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('state', $venue->state) }}" :required="!online">
                    </div>

                    <!-- Post Code -->
                    <div class="mb-4">
                        <label for="postcode" class="block text-gray-700 font-bold mb-2">Post Code <span x-show="online" style="display: none" class="font-normal text-sm">(optional for online)</span></label>
                        <input type="text" id="postcode" name="postcode"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('postcode', $venue->postcode) }}" :required="!online">
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
                            <option value="{{ $country['name'] }}" {{ old('country', $venue->country) ==
                                $country['name'] ? 'selected' : '' }}>
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
                            value="{{ old('location_geocode', $venue->location_geocode) }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Contact Name -->
                    <div class="mb-4">
                        <label for="vcontact_person" class="block text-gray-700 font-bold mb-2">Contact Name</label>
                        <input type="text" id="vcontact_person" name="vcontact_person"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('vcontact_person', $venue->vcontact_person) }}">
                    </div>

                    <!-- Phone -->
                    <div class="mb-4">
                        <label for="vcontact_phone" class="block text-gray-700 font-bold mb-2">Phone</label>
                        <input type="text" id="vcontact_phone" name="vcontact_phone"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('vcontact_phone', $venue->vcontact_phone) }}">
                    </div>

                    <!-- Post Code -->
                    <div class="mb-4">
                        <label for="vcontact_email" class="block text-gray-700 font-bold mb-2">Email</label>
                        <input type="text" id="vcontact_email" name="vcontact_email"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('vcontact_email', $venue->vcontact_email) }}">
                    </div>
                </div>

                <!-- Virtual venue -->
                <div class="mb-4">
                    <label class="flex items-start gap-2 text-gray-700">
                        <input type="checkbox" name="is_virtual" value="1" class="mt-1" x-model="online" {{ old('is_virtual', $venue->is_virtual) ? 'checked' : '' }}>
                        <span><span class="font-bold">Online / virtual venue</span> (e.g. Zoom). Several events may run here at the same time; facilitators still cannot be double-booked. Address, city, state and post code are optional for online venues.</span>
                    </label>
                </div>

                <!-- Remarks -->
                <div class="mb-4">
                    <label for="remarks" class="block text-gray-700 font-bold mb-2">Remarks</label>
                    <textarea id="remarks" name="remarks"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200 h-24">{{ old('remarks', $venue->remarks) }}</textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update Venue
                    </button>
                    <a href="{{ route('venues.show', $venue->id) }}"
                        class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout>
