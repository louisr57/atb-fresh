@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Courses
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Courses List</h1>

        <!-- Display courses in a table -->
        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <!-- Action column -->
                    <th class="border px-4 py-2 sticky left-0 text-blue-500 bg-gray-100 z-10">
                        Action
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('courses.index', ['sort_by' => 'id', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">ID
                            @if ($sort_by === 'id')
                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('courses.index', ['sort_by' => 'course_title', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Course name
                            @if ($sort_by === 'course_title')
                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                            @endif</a>
                        </a>
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('courses.index', ['sort_by' => 'duration', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Duration in Days
                            @if ($sort_by === 'duration')
                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                            @endif</a>
                        </a>
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left">
                        <a href="{{ route('courses.index', ['sort_by' => 'description', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Description
                            @if ($sort_by === 'description')
                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                            @endif</a>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                <tr class="hover:bg-gray-100">
                    <!-- Action column with sticky left positioning -->
                    <td class="border px-4 py-2 sticky left-0 bg-white z-10">
                        <a href="{{ route('courses.show', $course->id) }}" class="text-blue-600 hover:underline">
                            View
                        </a>
                    </td>
                    <td class="border border-gray-300 px-4 py-2 text-left">{{ $course->id }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-left">{{ $course->course_title }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-left">{{ $course->duration }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-left">{{ $course->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-layout>