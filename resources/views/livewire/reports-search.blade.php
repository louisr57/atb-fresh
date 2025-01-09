<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold mb-0 mt-1">Reports</h1>
        <button type="button"
            wire:click="toggleSearch"
            class="mb-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded flex items-center">
            <span>{{ $showSearch ? 'Hide Search' : 'Show Search' }}</span>
        </button>
    </div>

    <!-- Search Form -->
    <div x-show="$wire.showSearch"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="bg-sky-200 border border-blue-800 border-solid p-4 rounded-lg shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Course Title -->
            <div>
                <label for="search_course" class="block text-sm font-medium text-gray-700 mb-1">Course Title</label>
                <select wire:model.live="search_course" id="search_course" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">Select Course</option>
                    @if($search_course && !$courses->contains($search_course))
                        <option value="{{ $search_course }}" selected>{{ $search_course }}</option>
                    @endif
                    @foreach($courses as $course)
                        <option value="{{ $course }}">{{ $course }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Registration Status -->
            <div>
                <label for="search_status" class="block text-sm font-medium text-gray-700 mb-1">Registration Status</label>
                <select wire:model.live="search_status" id="search_status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">Select Status</option>
                    @if($search_status && !$statuses->contains($search_status))
                        <option value="{{ $search_status }}" selected>{{ $search_status }}</option>
                    @endif
                    @foreach($statuses as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Event Date -->
            <div>
                <label for="search_date" class="block text-sm font-medium text-gray-700 mb-1">Event Date</label>
                <div class="flex gap-2">
                    <select wire:model.live="search_date_operator" id="search_date_operator" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        @foreach($dateOperators as $operator)
                            <option value="{{ $operator }}">{{ $operator }}</option>
                        @endforeach
                    </select>
                    <input type="date" wire:model.live="search_date" id="search_date" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </div>
            </div>

            <!-- Student City -->
            <div>
                <label for="search_student_city" class="block text-sm font-medium text-gray-700 mb-1">Student City</label>
                <select wire:model.live="search_student_city" id="search_student_city" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">Select City</option>
                    @if($search_student_city && !$studentCities->contains($search_student_city))
                        <option value="{{ $search_student_city }}" selected>{{ $search_student_city }}</option>
                    @endif
                    @foreach($studentCities as $city)
                        <option value="{{ $city }}">{{ $city }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Student Country -->
            <div>
                <label for="search_student_country" class="block text-sm font-medium text-gray-700 mb-1">Student Country</label>
                <select wire:model.live="search_student_country" id="search_student_country" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">Select Country</option>
                    @if($search_student_country && !$studentCountries->contains($search_student_country))
                        <option value="{{ $search_student_country }}" selected>{{ $search_student_country }}</option>
                    @endif
                    @foreach($studentCountries as $country)
                        <option value="{{ $country }}">{{ $country }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Venue Name -->
            <div>
                <label for="search_venue_name" class="block text-sm font-medium text-gray-700 mb-1">Venue Name</label>
                <select wire:model.live="search_venue_name" id="search_venue_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">Select Venue</option>
                    @if($search_venue_name && !$venueNames->contains($search_venue_name))
                        <option value="{{ $search_venue_name }}" selected>{{ $search_venue_name }}</option>
                    @endif
                    @foreach($venueNames as $venue)
                        <option value="{{ $venue }}">{{ $venue }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Venue City -->
            <div>
                <label for="search_venue_city" class="block text-sm font-medium text-gray-700 mb-1">Venue City</label>
                <select wire:model.live="search_venue_city" id="search_venue_city" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">Select City</option>
                    @if($search_venue_city && !$venueCities->contains($search_venue_city))
                        <option value="{{ $search_venue_city }}" selected>{{ $search_venue_city }}</option>
                    @endif
                    @foreach($venueCities as $city)
                        <option value="{{ $city }}">{{ $city }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Venue State -->
            <div>
                <label for="search_venue_state" class="block text-sm font-medium text-gray-700 mb-1">Venue State</label>
                <select wire:model.live="search_venue_state" id="search_venue_state" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">Select State</option>
                    @if($search_venue_state && !$venueStates->contains($search_venue_state))
                        <option value="{{ $search_venue_state }}" selected>{{ $search_venue_state }}</option>
                    @endif
                    @foreach($venueStates as $state)
                        <option value="{{ $state }}">{{ $state }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Venue Country -->
            <div>
                <label for="search_venue_country" class="block text-sm font-medium text-gray-700 mb-1">Venue Country</label>
                <select wire:model.live="search_venue_country" id="search_venue_country" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">Select Country</option>
                    @if($search_venue_country && !$venueCountries->contains($search_venue_country))
                        <option value="{{ $search_venue_country }}" selected>{{ $search_venue_country }}</option>
                    @endif
                    @foreach($venueCountries as $country)
                        <option value="{{ $country }}">{{ $country }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Button -->
            <div class="md:col-span-3">
                <button wire:click="resetSearch" type="button" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="relative overflow-x-auto">
        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-4 py-2">Student Name</th>
                    <th class="border px-4 py-2">Email</th>
                    <th class="border px-4 py-2">Course</th>
                    <th class="border px-4 py-2">Status</th>
                    <th class="border px-4 py-2">Event Date</th>
                    <th class="border px-4 py-2">Student Location</th>
                    <th class="border px-4 py-2">Venue Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($results as $result)
                    <tr class="hover:bg-gray-100">
                        <td class="border px-4 py-2">{{ $result->first_name }} {{ $result->last_name }}</td>
                        <td class="border px-4 py-2">{{ $result->email }}</td>
                        <td class="border px-4 py-2">{{ $result->course_title }}</td>
                        <td class="border px-4 py-2">{{ $result->end_status }}</td>
                        <td class="border px-4 py-2">{{ $result->datefrom }}</td>
                        <td class="border px-4 py-2">{{ $result->student_city }}, {{ $result->student_country }}</td>
                        <td class="border px-4 py-2">
                            {{ $result->venue_name }}<br>
                            {{ $result->venue_city }}, {{ $result->venue_state }}<br>
                            {{ $result->venue_country }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $results->links() }}
    </div>
</div>
