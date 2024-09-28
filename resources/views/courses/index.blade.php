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
                    <th class="border px-4 py-2">ID</th>
                    <th class="border px-4 py-2">Course Title</th>
                    <th class="border px-4 py-2">Duration</th>
                    <th class="border px-4 py-2">Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                <tr class="hover:bg-gray-100">
                    <td class="border px-4 py-2">{{ $course->id }}</td>
                    <td class="border px-4 py-2">{{ $course->course_title }}</td>
                    <td class="border px-4 py-2">{{ $course->duration }}</td>
                    <td class="border px-4 py-2">{{ $course->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</x-layout>