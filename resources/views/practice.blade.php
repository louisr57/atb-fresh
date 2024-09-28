<x-layout>
    <x-slot name="heading">
        Testing Area
    </x-slot>

    <body>
        <nav>
            <a href="{{ route('practice') }}">Home</a>
            <a href="{{ route('about') }}">About</a>
            <a href="{{ route('contact') }}">Contact</a>
        </nav>

        {{ phpinfo(); }}


    </body>

</x-layout>