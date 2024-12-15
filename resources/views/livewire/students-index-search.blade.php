<div>
    <div class="mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold mb-0">Students List</h1>
            <input type="text"
                   wire:model.live="search"
                   class="w-full max-w-md shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   placeholder="Search by student name...">
            @if($search)
                <button wire:click="$set('search', '')" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                    ×
                </button>
            @endif
            <a href="{{ route('students.create') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Create New Student
            </a>
        </div>
    </div>

    @if($students->isEmpty())
        <h1>No students found.</h1>
    @else
        <table class="min-w-full table-auto border-collapse border border-gray-500">
            <thead class="bg-gray-200">
                <tr>
                    @if(auth()->user()->name === 'Louisr57')
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'reg_count', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Reg Count</a>
                    </th>
                    @endif
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'first_name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">First Name</a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'last_name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Last Name</a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'email', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Email</a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'phone_number', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">Phone Number</a>
                    </th>
                    <th class="border border-gray-500 px-4 py-2 text-left">
                        <a href="{{ route('students.index', ['sort_by' => 'city', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                            class="text-blue-500 hover:underline">City</a>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach($students as $student)
                <tr data-student-id="{{ $student->id }}"
                    onclick="window.location='{{ route('students.show', $student->id) }}'"
                    class="hover:bg-cyan-100 hover:underline cursor-pointer">
                    @if(auth()->user()->name === 'Louisr57')
                    <td class="border border-gray-500 px-4 py-2">{{ $student->reg_count }}</td>
                    @endif
                    <td class="border border-gray-500 px-4 py-2">{{ $student->first_name }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $student->last_name }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $student->email }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $student->phone_number }}</td>
                    <td class="border border-gray-500 px-4 py-2">{{ $student->city }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $students->appends(request()->input())->links() }}
        </div>
    @endif
</div>
