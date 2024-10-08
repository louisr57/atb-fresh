@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Courses
    </x-slot:heading>

    <div class="container overflow-x-auto mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold mb-6">Courses List</h1>
            <a href="{{ route('courses.create') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                New
            </a>
        </div>
        {{-- <h1 class="text-2xl font-bold mb-6">Courses List</h1> --}}

        @if($courses->isEmpty())
        <h1>No courses available.</h1>
        @else
        <!-- Display courses in a table -->
        <table class="min-w-full table-auto border-collapse border border-gray-500">
            <thead class="bg-gray-200">
                <tr>
                    <!-- Action column -->
                    <th class="border border-gray-500 px-4 py-2 sticky left-0 text-blue-500 z-10">
                        Action
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('courses.index', ['sort_by' => 'id', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">ID
                            @if ($sort_by === 'id')
                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('courses.index', ['sort_by' => 'course_title', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Course name
                            @if ($sort_by === 'course_title')
                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                            @endif</a>
                        </a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('courses.index', ['sort_by' => 'duration', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Duration in Days
                            @if ($sort_by === 'duration')
                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                            @endif</a>
                        </a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('courses.index', ['sort_by' => 'description', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Description
                            @if ($sort_by === 'description')
                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                            @endif</a>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-gray-50">
                @foreach($courses as $course)
                <tr class="hover:bg-sky-200 registration-row">
                    <!-- Action column with sticky left positioning -->
                    <td class="text-center border border-gray-500 px-4 py-2 sticky left-0 z-10">
                        <a href="{{ route('courses.show', $course->id) }}" class="text-blue-600 hover:underline">
                            View
                        </a>
                    </td>
                    <td class="border border-gray-500 px-4 py-2 text-left">{{ $course->id }}</td>
                    <td
                        class="border border-gray-500 px-4 py-2 text-left whitespace-normal break-words max-w-xs lg:max-w-md xl:max-w-lg">
                        {{ $course->course_title }}</td>
                    <td class="border border-gray-500 px-4 py-2 text-center">{{
                        number_format($course->duration, 1)
                        }}</td>
                    <td class="border border-gray-500 px-4 py-2 text-left">{{ $course->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="mt-4">
            {{ $courses->appends(request()->input())->links() }}
            <!-- Pagination with query strings -->
        </div>

        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
            {{ session('success') }}
        </div>
        @endif

    </div>

</x-layout>