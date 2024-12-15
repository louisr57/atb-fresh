<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100" x-data>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>

    @livewireStyles

    <style>
        .highlight {
            background-color: #acd2f1;
        }
    </style>


    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="h-full antialiased">
    <div class="min-h-full bg-slate-200">
        <nav class="bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <img class="h-8 w-8" src="https://laracasts.com/images/logo/logo-triangle.svg"
                                alt="Your Company">
                        </div>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4">
                                <x-nav-link href="/" :active="request()->is('/')">Home</x-nav-link>
                                <x-nav-link href="/students" :active="request()->is('students')">Students</x-nav-link>
                                {{-- <x-nav-link href="/registrations" :active="request()->is('registrations')">Registrations
                                </x-nav-link> --}}
                                <x-nav-link href="/events" :active="request()->is('events')">Events
                                </x-nav-link>
                                <x-nav-link href="/venues" :active="request()->is('venues')">Venues
                                </x-nav-link>
                                <x-nav-link href="/courses" :active="request()->is('courses')">Courses
                                </x-nav-link>
                                <x-nav-link href="/facilitators" :active="request()->is('facilitators')">Facilitators
                                </x-nav-link>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6">

                            @auth
                            <div class="hidden md:block">
                                <div class="ml-4 flex items-center md:ml-6">
                                    <span class="text-white mr-4">Welcome {{ Auth::user()->name }}</span>
                                    <!-- Profile dropdown -->
                                    <div class="relative ml-3" x-data="{ open: false }">
                                        <div>
                                            <button @click="open = !open" type="button"
                                                class="relative flex max-w-xs items-center rounded-full bg-gray-800 text-sm text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                                                id="user-menu-button">
                                                <span class="absolute -inset-1.5"></span>
                                                <span class="sr-only">Open user menu</span>
                                                <img class="h-8 w-8 rounded-full"
                                                    src="https://laracasts.com/images/lary-ai-face.svg" alt="">
                                            </button>
                                        </div>

                                        <div x-show="open" @click.away="open = false"
                                            class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5"
                                            role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button"
                                            tabindex="-1">

                                            @if(!request()->routeIs('dashboard'))
                                            <a href="{{ route('dashboard') }}"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
                                            @endif

                                            @if(!request()->routeIs('profile.edit'))
                                            <a href="{{ route('profile.edit') }}"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                            @endif

                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit"
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Log Out
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endauth
                        </div>
                    </div>
                    <div class="-mr-2 flex md:hidden">
                        <!-- Mobile menu button -->
                        <button type="button"
                            class="relative inline-flex items-center justify-center rounded-md bg-gray-800 p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                            aria-controls="mobile-menu" aria-expanded="false">
                            <span class="absolute -inset-0.5"></span>
                            <span class="sr-only">Open main menu</span>
                            <!-- Menu open: "hidden", Menu closed: "block" -->
                            <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <!-- Menu open: "block", Menu closed: "hidden" -->
                            <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state. -->
            <div class="md:hidden" id="mobile-menu">
                <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3">
                    <a href="/"
                        class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Home</a>
                    <a href="/about"
                        class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">About</a>
                    <a href="/contact"
                        class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Contact</a>
                </div>
                <div class="border-t border-gray-700 pb-3 pt-4">
                    <div class="flex items-center px-5">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full" src="https://laracasts.com/images/lary-ai-face.svg"
                                alt="">
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium leading-none text-white">Lary Robot</div>
                            <div class="text-sm font-medium leading-none text-gray-400">louisr57@gmail.com</div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-gray-400 shadow">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $heading }}</h1>
            </div>
        </header>
        <main>
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.querySelectorAll('.registration-row');
            if (rows.length > 0) {
                let currentIndex = null;

                function highlightRow(index) {
                    if (currentIndex !== null) {
                        rows[currentIndex].classList.remove('highlight');
                    }
                    currentIndex = index;
                    rows[currentIndex].classList.add('highlight');
                }

                rows.forEach((row, index) => {
                    row.addEventListener('click', function () {
                        highlightRow(index);
                    });

                    row.addEventListener('mouseover', function () {
                        row.classList.add('highlight');
                    });

                    row.addEventListener('mouseout', function () {
                        if (index !== currentIndex) {
                            row.classList.remove('highlight');
                        }
                    });
                });

                document.addEventListener('keydown', function (event) {
                    if (currentIndex !== null) {
                        if (event.key === 'ArrowDown') {
                            if (currentIndex < rows.length - 1) {
                                highlightRow(currentIndex + 1);
                            }
                        } else if (event.key === 'ArrowUp') {
                            if (currentIndex > 0) {
                                highlightRow(currentIndex - 1);
                            }
                        }
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
