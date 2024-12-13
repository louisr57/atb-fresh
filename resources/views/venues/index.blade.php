@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Venues
    </x-slot:heading>

    <div class="container mx-auto p-4" x-data="{
        showSearch: {{ request('show_search', 'false') }} || false,
        resetSearch() {
            window.location.href = '{{ route('venues.index') }}?show_search=true';
        }
    }">
        <!-- Flash Message Section -->
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            {{ session('error') }}
        </div>
        @endif

        <!-- Header section with buttons -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold mb-0 mt-1">Venues List</h1>
            <div class="flex space-x-4">
                <button type="button"
                    @click="showSearch = !showSearch"
                    class="mb-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded flex items-center">
                    <span x-text="showSearch ? 'Hide Search' : 'Show Search'"></span>
                </button>
                <a href="{{ route('venues.create') }}"
                    class="mb-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create New Venue
                </a>
            </div>
        </div>

        <!-- Search Form -->
        <div x-show="showSearch"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-white p-4 rounded-lg shadow mb-6"
             style="display: none;">
            <form action="{{ route('venues.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4" id="searchForm">
                <!-- Preserve sort parameters -->
                <input type="hidden" name="sort_by" value="{{ request('sort_by', 'venue_name') }}">
                <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">

                <!-- Venue Name -->
                <div>
                    <label for="search_venue" class="block text-sm font-medium text-gray-700 mb-1">Venue Name</label>
                    <livewire:venue-search-dropdown
                        field-type="venue"
                        name="search_venue"
                        placeholder="Search venue name..."
                    />
                </div>

                <!-- City -->
                <div>
                    <label for="search_city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <livewire:venue-search-dropdown
                        field-type="city"
                        name="search_city"
                        placeholder="Search city..."
                    />
                </div>

                <!-- Country -->
                <div>
                    <label for="search_country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <livewire:venue-search-dropdown
                        field-type="country"
                        name="search_country"
                        placeholder="Search country..."
                    />
                </div>

                <!-- Search and Reset Buttons -->
                <div class="md:col-span-3 flex items-end space-x-4">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Search
                    </button>
                    <button type="button"
                        @click="resetSearch()"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Reset
                    </button>
                </div>
            </form>
        </div>

        @if($venues->isEmpty())
        <h1>No venues available.</h1>
        @else
        <!-- Display venues in a table -->
        <div class="relative overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-500">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border border-gray-500 px-4 py-2 sticky left-0 text-blue-500 z-10">
                            Actions
                        </th>
                        <th class="border border-gray-500 px-4 py-2 text-left">
                            <a href="{{ route('venues.index', array_merge(request()->except(['sort_by', 'direction']), ['sort_by' => 'venue_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-blue-500 hover:underline">Venue Name
                                @if ($sort_by === 'venue_name')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border border-gray-500 px-4 py-2 text-left">
                            <a href="{{ route('venues.index', array_merge(request()->except(['sort_by', 'direction']), ['sort_by' => 'city', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-blue-500 hover:underline">City
                                @if ($sort_by === 'city')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border border-gray-500 px-4 py-2 text-left">
                            <a href="{{ route('venues.index', array_merge(request()->except(['sort_by', 'direction']), ['sort_by' => 'country', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}"
                                class="text-blue-500 hover:underline">Country
                                @if ($sort_by === 'country')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border border-gray-500 px-4 py-2 text-left">Address</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-50">
                    @foreach($venues as $venue)
                    <tr class="hover:bg-sky-100">
                        <td class="text-center border border-gray-500 px-4 py-2 sticky left-0 z-10 bg-gray-50">
                            <div class="flex gap-2 justify-center">
                                <a href="{{ route('venues.show', $venue->id) }}" class="text-blue-600 hover:underline">
                                    View
                                </a>
                            </div>
                        </td>
                        <td class="border border-gray-500 px-4 py-2">{{ $venue->venue_name }}</td>
                        <td class="border border-gray-500 px-4 py-2">{{ $venue->city }}</td>
                        <td class="border border-gray-500 px-4 py-2">{{ $venue->country }}</td>
                        <td class="border border-gray-500 px-4 py-2">{{ $venue->address }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="mt-4">
            {{ $venues->appends(request()->input())->links() }}
        </div>
    </div>
</x-layout>
