@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Students
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold mb-0">Students List</h1>
            <a href="{{ route('students.create') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Create New Student
            </a>
        </div>

        @if($students->isEmpty())
        <h1>No students available.</h1>
        @else
        <table class="min-w-full table-auto border-collapse border border-gray-500">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'first_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">First Name</a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'last_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Last Name</a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'email', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Email</a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'phone_number', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Phone Number</a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'city', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">City</a>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach($students as $student)
                <tr data-student-id="{{ $student->id }}"
                    onclick="window.location='{{ route('students.show', $student->id) }}'"
                    class="hover:bg-cyan-100 hover:underline cursor-pointer">
                    <!-- Action column with sticky left positioning -->
                    {{-- <td class="border px-4 py-2 sticky left-0 bg-white z-10">
                        <a href="{{ route('students.show', $student->id) }}" class="text-blue-600 hover:underline">
                            View
                        </a>
                    </td> --}}
                    <td class="border border-gray-500 px-4 py-2">{{ $student->first_name }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $student->last_name }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $student->email }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $student->phone_number }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $student->city }}</td>
                </tr>
                <script>
                    console.log('Rendered student row with ID: {{ $student->id }}');
                </script>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="mt-4">
            {{ $students->appends(request()->input())->links() }}
            <!-- Pagination with query strings -->
        </div>
    </div>

    <script>
        // Ensure DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get the highlight parameter from the query string
    const urlParams = new URLSearchParams(window.location.search);
    const highlightId = urlParams.get('highlight');

    if (highlightId) {
        // Find the row that matches the student ID
        const row = document.querySelector(`tr[data-student-id="${highlightId}"]`);
        if (row) {
            // Add a highlight class to the row
            row.classList.add('bg-yellow-500');

            // Scroll the row into view smoothly after a small delay to ensure full rendering
            setTimeout(() => {
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 500);

            // Remove the highlight when the user clicks anywhere on the page
            document.addEventListener('click', function() {
                row.classList.remove('bg-yellow-500');
            }, { once: true });
        }
    }
});
    </script>

</x-layout>