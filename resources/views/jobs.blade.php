<x-layout>
    <x-slot name="heading">
        Job Listing
    </x-slot>
    <ul>

        @foreach($jobs as $job)
        <li>
            <a href="/jobs/{{ $job['id'] }}" class="text-blue-500 hover:underline">
                <strong>{{ $job['title'] }}:</strong> Pays {{ $job['salary']}} per year.
            </a>
        </li>
        @endforeach
    </ul>

</x-layout>