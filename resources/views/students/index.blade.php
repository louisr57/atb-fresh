@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Students
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-semibold mb-6">Students List</h1>

        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'first_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">First Name</a>
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'last_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Last Name</a>
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'email', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Email</a>
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'phone_number', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Phone Number</a>
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'city', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">City</a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                <tr onclick="window.location='{{ route('students.show', $student->id) }}'"
                    class="hover:bg-gray-100 cursor-pointer">
                    <td class="border border-gray-300 px-4 py-2">{{ $student->first_name }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $student->last_name }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $student->email }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $student->phone_number }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $student->city }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $students->appends(request()->input())->links() }}
            <!-- Pagination with query strings -->
        </div>
    </div>

</x-layout>
