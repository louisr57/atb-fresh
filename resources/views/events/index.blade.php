@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Calendar Events
    </x-slot:heading>

    <div class="container mx-auto p-4" x-data="{
        showSearch: {{ request('show_search', 'false') }} || false,
        resetSearch() {
            window.location.href = '{{ route('events.index') }}?show_search=true';
        }
    }">
        <!-- Header section with buttons -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold mb-0 mt-1">Events (Calendar) List</h1>
            <div class="flex space-x-4">
                <button type="button"
                    @click="showSearch = !showSearch"
                    class="mb-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded flex items-center">
                    <span x-text="showSearch ? 'Hide Search' : 'Show Search'"></span>
                </button>
                <a href="{{ route('events.create') }}"
                    class="mb-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create New Event
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
            <form action="{{ route('events.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4" id="searchForm">
                <!-- Preserve sort parameters -->
                <input type="hidden" name="sort_by" value="{{ request('sort_by', 'course_title') }}">
                <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">

                <!-- Course Title -->
                <div>
                    <label for="search_course" class="block text-sm font-medium text-gray-700 mb-1">Course Title</label>
                    <livewire:event-search-dropdown
                        field-type="course"
                        name="search_course"
                        placeholder="Search course title..."
                    />
                </div>

                <!-- Facilitator Name -->
                <div>
                    <label for="search_facilitator" class="block text-sm font-medium text-gray-700 mb-1">Facilitator Name</label>
                    <livewire:event-search-dropdown
                        field-type="facilitator"
                        name="search_facilitator"
                        placeholder="Search facilitator..."
                    />
                </div>

                <!-- Start Date -->
                <div>
                    <label for="search_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="search_date" id="search_date" value="{{ request('search_date') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </div>

                <!-- Venue -->
                <div>
                    <label for="search_venue" class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
                    <livewire:event-search-dropdown
                        field-type="venue"
                        name="search_venue"
                        placeholder="Search venue..."
                    />
                </div>

                <!-- City -->
                <div>
                    <label for="search_city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <livewire:event-search-dropdown
                        field-type="city"
                        name="search_city"
                        placeholder="Search city..."
                    />
                </div>

                <!-- State -->
                <div>
                    <label for="search_state" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                    <livewire:event-search-dropdown
                        field-type="state"
                        name="search_state"
                        placeholder="Search state..."
                    />
                </div>

                <!-- Country -->
                <div>
                    <label for="search_country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <livewire:event-search-dropdown
                        field-type="country"
                        name="search_country"
                        placeholder="Search country..."
                    />
                </div>

                <!-- Search and Reset Buttons -->
                <div class="md:col-span-2 flex items-end space-x-4">
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

        <!-- Wrapping the table inside a div to make it horizontally scrollable -->
        <div class="relative overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <!-- Action column -->
                        <th class="border px-4 py-2 sticky left-0 bg-gray-100 z-10">
                            Action
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('events.index', array_merge(request()->except(['sort_by', 'direction']), ['sort_by' => 'course_title', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}">
                                Course Title
                                @if ($sort_by === 'course_title')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('events.index', array_merge(request()->except(['sort_by', 'direction']), ['sort_by' => 'facilitator_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}">
                                Facilitator Name
                                @if ($sort_by === 'facilitator_name')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('events.index', array_merge(request()->except(['sort_by', 'direction']), ['sort_by' => 'participant_count', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}">
                                Participant Count
                                @if ($sort_by === 'participant_count')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('events.index', array_merge(request()->except(['sort_by', 'direction']), ['sort_by' => 'datefrom', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}">
                                Start Date
                                @if ($sort_by === 'datefrom')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('events.index', array_merge(request()->except(['sort_by', 'direction']), ['sort_by' => 'dateto', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}">
                                End Date
                                @if ($sort_by === 'dateto')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">
                            <a
                                href="{{ route('events.index', array_merge(request()->except(['sort_by', 'direction']), ['sort_by' => 'venue_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}">
                                Venue
                                @if ($sort_by === 'venue_name')
                                <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        </th>
                        <th class="border px-4 py-2">City</th>
                        <th class="border px-4 py-2">State</th>
                        <th class="border px-4 py-2">Country</th>
                        <th class="border px-4 py-2">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($events as $event)
                    <tr class="hover:bg-gray-100 registration-row">
                        <!-- Action column with sticky left positioning -->
                        <td class="border px-4 py-2 sticky left-0 bg-white z-10">
                            <a href="{{ route('events.show', $event->id) }}" class="text-blue-600 hover:underline">
                                View
                            </a>
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $event->course->course_title }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $event->facilitator->first_name }} {{ $event->facilitator->last_name }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $event->participant_count }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $event->datefrom }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $event->dateto ?? 'N/A' }}
                        </td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->venue->venue_name ?? 'N/A' }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->venue->city ?? 'N/A' }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->venue->state ?? 'N/A' }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">{{ $event->venue->country ?? 'N/A' }}</td>
                        <td class="border px-4 py-2 whitespace-nowrap">
                            {{ $event->remarks ?? 'No remarks' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $events->appends(request()->input())->links() }}
        </div>
    </div>
</x-layout>
