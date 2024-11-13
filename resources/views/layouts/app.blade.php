<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
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
                                <div class="relative" x-data="{ open: false }">
                                    <x-nav-link @click="open = !open" :active="request()->is('/')"
                                        class="cursor-pointer">
                                        Home
                                        <svg class="ml-1 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </x-nav-link>

                                    <div x-show="open" @click.away="open = false"
                                        class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                        <a href="#"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Option 1</a>
                                        <a href="#"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Option 2</a>
                                    </div>
                                </div>

                                <div class="relative" x-data="{ open: false }">
                                    <x-nav-link @click="open = !open" :active="request()->is('students')"
                                        class="cursor-pointer">
                                        ATB Students
                                        <svg class="ml-1 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </x-nav-link>

                                    <div x-show="open" @click.away="open = false"
                                        class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View
                                            All Students</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add
                                            New Student</a>
                                    </div>
                                </div>

                                <div class="relative" x-data="{ open: false }">
                                    <x-nav-link @click="open = !open" :active="request()->is('registrations')"
                                        class="cursor-pointer">
                                        ATB Course Registrations
                                        <svg class="ml-1 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </x-nav-link>

                                    <div x-show="open" @click.away="open = false"
                                        class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View
                                            All Registrations</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add
                                            New Registration</a>
                                    </div>
                                </div>

                                <div class="relative" x-data="{ open: false }">
                                    <x-nav-link @click="open = !open" :active="request()->is('events')"
                                        class="cursor-pointer">
                                        ATB Calendar Events
                                        <svg class="ml-1 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </x-nav-link>

                                    <div x-show="open" @click.away="open = false"
                                        class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View
                                            All Events</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add
                                            New Event</a>
                                    </div>
                                </div>

                                <div class="relative" x-data="{ open: false }">
                                    <x-nav-link @click="open = !open" :active="request()->is('venues')"
                                        class="cursor-pointer">
                                        ATB Venues
                                        <svg class="ml-1 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </x-nav-link>

                                    <div x-show="open" @click.away="open = false"
                                        class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View
                                            All Venues</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add
                                            New Venue</a>
                                    </div>
                                </div>

                                <div class="relative" x-data="{ open: false }">
                                    <x-nav-link @click="open = !open" :active="request()->is('courses')"
                                        class="cursor-pointer">
                                        Courses
                                        <svg class="ml-1 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </x-nav-link>

                                    <div x-show="open" @click.away="open = false"
                                        class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View
                                            All Courses</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add
                                            New Course</a>
                                    </div>
                                </div>

                                <div class="relative" x-data="{ open: false }">
                                    <x-nav-link @click="open = !open" :active="request()->is('facilitators')"
                                        class="cursor-pointer">
                                        Facilitators
                                        <svg class="ml-1 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </x-nav-link>

                                    <div x-show="open" @click.away="open = false"
                                        class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View
                                            All Facilitators</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add
                                            New Facilitator</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @auth
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6">
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
                                    tabindex="-1" style="display: none;">

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

                    <!-- Mobile menu button -->
                    <div class="-mr-2 flex md:hidden">
                        <button type="button" @click="open = !open"
                            class="relative inline-flex items-center justify-center rounded-md bg-gray-800 p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                            aria-controls="mobile-menu" aria-expanded="false">
                            <span class="absolute -inset-0.5"></span>
                            <span class="sr-only">Open main menu</span>
                            <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="md:hidden" id="mobile-menu" x-show="open" style="display: none;">
                <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3">
                    <x-responsive-nav-link href="/" :active="request()->is('/')">Home</x-responsive-nav-link>
                    <x-responsive-nav-link href="/students" :active="request()->is('students')">ATB Students
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="/registrations" :active="request()->is('registrations')">ATB Course
                        Registrations</x-responsive-nav-link>
                    <x-responsive-nav-link href="/events" :active="request()->is('events')">ATB Calendar Events
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="/venues" :active="request()->is('venues')">ATB Venues
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="/courses" :active="request()->is('courses')">Courses
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="/facilitators" :active="request()->is('facilitators')">Facilitators
                    </x-responsive-nav-link>
                </div>

                @auth
                <div class="border-t border-gray-700 pb-3 pt-4">
                    <div class="flex items-center px-5">
                        <div class="flex-shrink-0">
                            <img class="h-10 w-10 rounded-full" src="https://laracasts.com/images/lary-ai-face.svg"
                                alt="">
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-white">{{ Auth::user()->name }}</div>
                            <div class="text-sm font-medium text-gray-400">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1 px-2">
                        @if(!request()->routeIs('dashboard'))
                        <x-responsive-nav-link href="{{ route('dashboard') }}">Dashboard</x-responsive-nav-link>
                        @endif

                        @if(!request()->routeIs('profile.edit'))
                        <x-responsive-nav-link href="{{ route('profile.edit') }}">Profile</x-responsive-nav-link>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-responsive-nav-link href="{{ route('logout') }}" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                Log Out
                            </x-responsive-nav-link>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
        </nav>

        @isset($header)
        <header class="bg-gray-400 shadow">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $header }}</h1>
            </div>
        </header>
        @endisset

        <!-- Page Content -->
        <main>
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>

</html>