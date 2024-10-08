<x-layout>
    <x-slot:heading>
        Create New Facilitator
    </x-slot:heading>

    <div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-lg mt-10">
        <h2 class="text-2xl font-semibold mb-6 text-center">Create New Facilitator</h2>

        <form action="{{ route('facilitators.store') }}" method="POST">
            @csrf
            <!-- First Name -->
            <div class="mb-4">
                <label for="first_name" class="block text-gray-700 font-medium mb-2">First Name</label>
                <input type="text" name="first_name" id="first_name"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- Last Name -->
            <div class="mb-4">
                <label for="last_name" class="block text-gray-700 font-medium mb-2">Last Name</label>
                <input type="text" name="last_name" id="last_name"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                <input type="email" name="email" id="email"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>

            <!-- Phone Number -->
            <div class="mb-4">
                <label for="phone_number" class="block text-gray-700 font-medium mb-2">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Address -->
            <div class="mb-4">
                <label for="address" class="block text-gray-700 font-medium mb-2">Address</label>
                <input type="text" name="address" id="address"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- City -->
            <div class="mb-4">
                <label for="city" class="block text-gray-700 font-medium mb-2">City</label>
                <input type="text" name="city" id="city"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- State -->
            <div class="mb-4">
                <label for="state" class="block text-gray-700 font-medium mb-2">State</label>
                <input type="text" name="state" id="state"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Country -->
            <div class="mb-4">
                <label for="country" class="block text-gray-700 font-medium mb-2">Country</label>
                <input type="text" name="country" id="country"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Post Code -->
            <div class="mb-6">
                <label for="post_code" class="block text-gray-700 font-medium mb-2">Post Code</label>
                <input type="text" name="post_code" id="post_code"
                    class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Create
                    facilitator</button>
            </div>
        </form>
    </div>

</x-layout>