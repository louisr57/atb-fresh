<x-layout>

    <x-slot:heading>
        ATB Courses
    </x-slot:heading>

    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ATB Courses') }}
        </h2>
    </x-slot> --}}

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Flash Message Section -->
                    @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        {{ session('success') }}
                    </div>
                    @endif

                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold mb-0 mt-1">Courses List</h1>
                        <a href="{{ route('courses.create') }}"
                            class="mb-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create New Course
                        </a>
                    </div>

                    @if($courses->isEmpty())
                    <h1>No courses available.</h1>
                    @else
                    <!-- Display courses in a table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border-collapse border border-gray-500">
                            <thead class="bg-gray-200">
                                <tr>
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
                                    </th>
                                    <th class="border border-gray-500 px-4 py-2 text-left">
                                        <a href="{{ route('courses.index', ['sort_by' => 'duration', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                                            class="text-blue-500 hover:underline">Duration in Days
                                            @if ($sort_by === 'duration')
                                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                            @endif</a>
                                    </th>
                                    <th class="border border-gray-500 px-4 py-2 text-left">
                                        <a href="{{ route('courses.index', ['sort_by' => 'description', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}"
                                            class="text-blue-500 hover:underline">Description
                                            @if ($sort_by === 'description')
                                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                            @endif</a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach($courses as $course)
                                <tr class="hover:bg-sky-200 cursor-pointer" onclick="window.location='{{ route('courses.show', $course->id) }}'">
                                    <td class="border border-gray-500 px-4 py-2 text-left">{{ $course->id }}</td>
                                    <td
                                        class="border border-gray-500 px-4 py-2 text-left whitespace-normal break-words max-w-xs lg:max-w-md xl:max-w-lg">
                                        {{ $course->course_title }}
                                    </td>
                                    <td class="border border-gray-500 px-4 py-2 text-center">
                                        {{ number_format($course->duration, 1) }}
                                    </td>
                                    <td class="border border-gray-500 px-4 py-2 text-left">{{ $course->description }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <div class="mt-4">
                        {{ $courses->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
