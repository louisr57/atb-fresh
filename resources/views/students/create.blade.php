<x-layout>
    <x-slot:heading>
        Create New Student
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Create New Student</h1>

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

        <form action="{{ route('students.store') }}" method="POST">
            @csrf

            <!-- First Name -->
            <div class="mb-4">
                <label for="first_name" class="block text-gray-700 font-bold mb-2">First Name</label>
                <input type="text" id="first_name" name="first_name"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                    value="{{ old('first_name') }}" required>
            </div>

            <!-- Last Name -->
            <div class="mb-4">
                <label for="last_name" class="block text-gray-700 font-bold mb-2">Last Name</label>
                <input type="text" id="last_name" name="last_name"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                    value="{{ old('last_name') }}" required>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                <input type="email" id="email" name="email"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                    value="{{ old('email') }}" required>
            </div>

            <!-- Phone Number -->
            <div class="mb-4">
                <label for="phone_number" class="block text-gray-700 font-bold mb-2">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                    value="{{ old('phone_number') }}" required>
            </div>

            <!-- Address -->
            <div class="mb-4">
                <label for="address" class="block text-gray-700 font-bold mb-2">Address</label>
                <input type="text" id="address" name="address"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                    value="{{ old('address') }}" required>
            </div>

            <!-- City -->
            <div class="mb-4">
                <label for="city" class="block text-gray-700 font-bold mb-2">City</label>
                <input type="text" id="city" name="city"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                    value="{{ old('city') }}" required>
            </div>

            <!-- State -->
            <div class="mb-4">
                <label for="state" class="block text-gray-700 font-bold mb-2">State</label>
                <input type="text" id="state" name="state"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                    value="{{ old('state') }}" required>
            </div>

            <!-- Country -->
            <div class="mb-4">
                <label for="country" class="block text-gray-700 font-bold mb-2">Country</label>
                <input type="text" id="country" name="country"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                    value="{{ old('country') }}" required>
            </div>

            <!-- Post Code -->
            <div class="mb-4">
                <label for="post_code" class="block text-gray-700 font-bold mb-2">Post Code</label>
                <input type="text" id="post_code" name="post_code"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"
                    value="{{ old('post_code') }}" required>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Submit
                </button>
            </div>
        </form>
    </div>

</x-layout>