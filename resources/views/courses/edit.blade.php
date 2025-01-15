<x-layout>
    <x-slot:heading>
        Edit Course
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Edit Course</h1>

        @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 mb-4 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <form action="{{ route('courses.update', $course->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="course_title" class="block text-gray-700 font-bold mb-2">Course Title</label>
                        <input type="text" id="course_title" name="course_title"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('course_title', $course->course_title) }}" required>
                    </div>

                    <div class="mb-4">
                        <label for="course_code" class="block text-gray-700 font-bold mb-2">Course Code</label>
                        <input type="text" id="course_code" name="course_code"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('course_code', $course->course_code) }}" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                    <textarea id="description" name="description"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200 h-32"
                        required>{{ old('description', $course->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="prerequisites" class="block text-gray-700 font-bold mb-2">Prerequisites</label>
                        <input type="text" id="prerequisites" name="prerequisites"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('prerequisites', $course->prerequisites) }}" required>
                    </div>

                    <div class="mb-4">
                        <label for="duration" class="block text-gray-700 font-bold mb-2">Duration (in days)</label>
                        <input type="number" id="duration" name="duration" step="0.1"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('duration', $course->duration) }}" required>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit"
                        class="mt-3 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Course
                    </button>
                    <a href="{{ route('courses.show', $course->id) }}"
                        class="mt-3 bg-red-500 text-white font-bold py-2 px-4 rounded hover:bg-red-600">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout>
