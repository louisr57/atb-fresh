@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Courses
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Courses List</h1>

        <!-- Check if there are any courses -->
        @if($courses->isEmpty())
        <p>No courses available.</p>
        @else
        <!-- Display courses in a table -->
        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('courses.index', ['sort_by' => 'id', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">ID</a>
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('courses.index', ['sort_by' => 'course_title', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Course name</a>
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('courses.index', ['sort_by' => 'duration', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Duration in Days</a>
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('courses.index', ['sort_by' => 'description', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Description</a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                <tr class="hover:bg-gray-100">
                    <td class="border border-gray-300 px-4 py-2 text-left">{{ $course->id }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-left">{{ $course->course_title }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-left">{{ $course->duration }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-left">{{ $course->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</x-layout>