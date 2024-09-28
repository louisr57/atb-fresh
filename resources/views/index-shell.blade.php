@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Students
    </x-slot:heading>

    @section('content')
    <div class="container">
        <h1>Students List</h1>

        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead>
                <tr class="hover:bg-gray-900">
                    <th class="border border-gray-300 px-4 py-2"><a
                            href="{{ route('students.index', ['sort_by' => 'first_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">First
                            Name</a></th>
                    <th class="border border-gray-300 px-4 py-2"><a
                            href="{{ route('students.index', ['sort_by' => 'last_name', 'direction'=> $direction =='asc' ? 'desc' : 'asc']) }}">Last
                            Name</a></th>
                    <th class="border border-gray-300 px-4 py-2"><a
                            href="{{ route('students.index', ['sort_by' => 'email', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">Email</a>
                    </th>
                    <th class="border border-gray-300 px-4 py-2"><a
                            href="{{ route('students.index', ['sort_by' => 'phone_number', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">Phone
                            Number</a></th>
                    <th class="border border-gray-300 px-4 py-2"><a
                            href="{{ route('students.index', ['sort_by' => 'city', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">City</a>
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($students as $student)
                <tr onclick="window.location='{{ route('students.show', $student->id) }}'" style="cursor:pointer;">
                    <td>{{ $student->first_name }}</td>
                    <td>{{ $student->last_name }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->phone_number }}</td>
                    <td>{{ $student->city }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $students->appends(request()->input())->links() }}
        <!--Pagination with query strings-->
    </div>

    @endsection
    </x-Components /layout>
