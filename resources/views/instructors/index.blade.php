@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        Instructors
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Instructors List</h1>

        @if($instructors->isEmpty())
        <p>No instructors available.</p>
        @else
        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-4 py-2">ID</th>
                    <th class="border px-4 py-2">First Name</th>
                    <th class="border px-4 py-2">Last Name</th>
                    <th class="border px-4 py-2">Email</th>
                    <th class="border px-4 py-2">View</th>
                </tr>
            </thead>
            <tbody>
                @foreach($instructors as $instructor)
                <tr class="hover:bg-gray-100">
                    <td class="border px-4 py-2">{{ $instructor->id }}</td>
                    <td class="border px-4 py-2">{{ $instructor->first_name }}</td>
                    <td class="border px-4 py-2">{{ $instructor->last_name }}</td>
                    <td class="border px-4 py-2">{{ $instructor->email }}</td>
                    <td class="border px-4 py-2">
                        <a href="{{ route('instructors.show', $instructor->id) }}"
                            class="text-blue-600 hover:underline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</x-layout>