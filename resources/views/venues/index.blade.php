@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Venues
    </x-slot:heading>

    <div class="container overflow-x-auto mx-auto p-6">

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

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold mb-0 mt-1">Venues List</h1>
            <a href="{{ route('venues.create') }}"
                class="mb-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Create New Venue
            </a>
        </div>

        @if($venues->isEmpty())
        <h1>No venues available.</h1>
        @else
        <!-- Display venues in a table -->
        <table class="min-w-full table-auto border-collapse border border-gray-500">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border border-gray-500 px-4 py-2 sticky left-0 text-blue-500 z-10">
                        Actions
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('venues.index', ['sort_by' => 'id', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">ID
                            @if ($sort_by === 'id')
                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('venues.index', ['sort_by' => 'venue_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Venue Name
                            @if ($sort_by === 'venue_name')
                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('venues.index', ['sort_by' => 'city', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">City
                            @if ($sort_by === 'city')
                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('venues.index', ['sort_by' => 'country', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
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
                            {{-- @if($venue->events->isEmpty())
                            <form action="{{ route('venues.destroy', $venue->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline"
                                    onclick="return confirm('Are you sure you want to delete this venue?')">
                                    Delete
                                </button>
                            </form>
                            @endif --}}
                        </div>
                    </td>
                    <td class="border border-gray-500 px-4 py-2">{{ $venue->id }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $venue->venue_name }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $venue->city }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $venue->country }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $venue->address }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="mt-4">
            {{ $venues->appends(request()->input())->links() }}
        </div>
    </div>
</x-layout>