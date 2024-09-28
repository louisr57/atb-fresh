<x-layout>

    <x-slot:heading>
        Student Details
    </x-slot:heading>

    <div class="container mx-auto p-6">
        <h1 class="font-bold text-3xl text-blue-800 mb-6">Student Details for {{ $student->first_name }} {{
            $student->last_name }}</h1>

        <table class="table-auto w-full text-left border border-collapse border-gray-300 mb-6">
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">First Name</th>
                <td class="border px-4 py-2">{{ $student->first_name }}</td>
            </tr>
            <tr>
                <th class="border px-4 py-2">Last Name</th>
                <td class="border px-4 py-2">{{ $student->last_name }}</td>
            </tr>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Email</th>
                <td class="border px-4 py-2">{{ $student->email }}</td>
            </tr>
            <tr>
                <th class="border px-4 py-2">Phone Number</th>
                <td class="border px-4 py-2">{{ $student->phone_number }}</td>
            </tr>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Address</th>
                <td class="border px-4 py-2">{{ $student->address }}</td>
            </tr>
            <tr>
                <th class="border px-4 py-2">City</th>
                <td class="border px-4 py-2">{{ $student->city }}</td>
            </tr>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">State</th>
                <td class="border px-4 py-2">{{ $student->state }}</td>
            </tr>
            <tr>
                <th class="border px-4 py-2">Country</th>
                <td class="border px-4 py-2">{{ $student->country }}</td>
            </tr>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Post Code</th>
                <td class="border px-4 py-2">{{ $student->post_code }}</td>
            </tr>
            <tr>
                <th class="border px-4 py-2">Website</th>
                <td class="border px-4 py-2">{{ $student->website }}</td>
            </tr>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Ident</th>
                <td class="border px-4 py-2">{{ $student->ident }}</td>
            </tr>
            <tr>
                <th class="border px-4 py-2">Next of Kin</th>
                <td class="border px-4 py-2">{{ $student->next_of_kin }}</td>
            </tr>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Allergies</th>
                <td class="border px-4 py-2">{{ $student->allergies }}</td>
            </tr>
            <tr>
                <th class="border px-4 py-2">Special Needs</th>
                <td class="border px-4 py-2">{{ $student->special_needs }}</td>
            </tr>
        </table>

        <a href="{{ route('students.index') }}"
            class="inline-block bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">
            &lt;&lt;&lt; Back to Students List
        </a>
    </div>


</x-layout>
