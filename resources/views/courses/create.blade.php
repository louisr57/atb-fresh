<x-layout>
    <x-slot:heading>
        Create New Course
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Create New Course</h1>

        <!-- Display validation errors, if any -->
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
            <form action="{{ route('courses.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Course Code -->
                    <div class="mb-4">
                        <label for="course_code" class="block text-gray-700 font-bold mb-2">Course Code</label>
                        <input type="text" id="course_code" name="course_code"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('course_code') }}" required>
                    </div>

                    <!-- Course Title -->
                    <div class="mb-4">
                        <label for="course_title" class="block text-gray-700 font-bold mb-2">Course Title</label>
                        <input type="text" id="course_title" name="course_title"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('course_title') }}" required>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                    <textarea id="description" name="description"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200 h-32"
                        required>{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Prerequisites -->
                    <div class="mb-4">
                        <label for="prerequisites" class="block text-gray-700 font-bold mb-2">Prerequisites</label>
                        <input type="text" id="prerequisites" name="prerequisites"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('prerequisites') }}" required>
                    </div>

                    <!-- Duration -->
                    <div class="mb-4">
                        <label for="duration" class="block text-gray-700 font-bold mb-2">Duration (in days)</label>
                        <input type="number" id="duration" name="duration" step="0.1"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            value="{{ old('duration') }}" required>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="mt-7 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Create Course
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>