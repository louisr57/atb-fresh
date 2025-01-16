@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        Facilitators
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <!-- Flash Message Section -->
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            {{ session('success') }}
        </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold mb-6">Facilitators List</h1>
            <a href="{{ route('facilitators.create') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Create New Facilitator
            </a>
        </div>
        @if($facilitators->isEmpty())
        <p>No Facilitators available.</p>
        @else
        <table class="min-w-full table-auto border-collapse border border-gray-500">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border border-gray-500 px-4 py-2 text-left text-blue-500 hover:underline">First Name</th>
                    <th class="border border-gray-500 px-4 py-2 text-left text-blue-500 hover:underline">Last Name</th>
                    <th class="border border-gray-500 px-4 py-2 text-left text-blue-500 hover:underline">Email</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach($facilitators as $facilitator)
                <tr class="hover:bg-sky-200 cursor-pointer" onclick="window.location='{{ route('facilitators.show', $facilitator->id) }}'">
                    <td class="border border-gray-500 px-4 py-2">{{ $facilitator->first_name }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $facilitator->last_name }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $facilitator->email }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</x-layout>
