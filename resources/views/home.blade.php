<x-layout>

    <x-slot:heading>
        Home
    </x-slot:heading>

    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Home') }}
        </h2>
    </x-slot> --}}

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-3xl text-blue-900 mb-6">What is ATB?</h2>

                    <div class="space-y-4 text-gray-700">
                        <p>
                            Awareness Through the Body (ATB), started in July 1992 as a program to improve the posture
                            of school children, but quickly evolved into a practice to help children develop their
                            capacity for attention, concentration and relaxation, while enhancing their ability for self
                            awareness and self-regulation.
                        </p>

                        <p>
                            ATB is a practice that assists children and adults to come to better know the complexity of
                            their own being and find ways to manage this complexity more effectively, so as to become a
                            more self-directed beings organized around their psychological center, the inmost, truer
                            part of their being.
                        </p>

                        <p>
                            The activities are creative and often fun. They develop gradually and encourage
                            concentration, focus, relaxation and a sense of accomplishment. The practice works by first
                            bringing the individuals in contact with their own body felt sensations and into a state of
                            receptivity in which they can better "listen" to the many and varied inputs they are
                            continuously receiving from both their inner and outer world. The exercises then allow them
                            to gradually discover the complex amalgam of which they are made and to find the tools to
                            manage this complexity effectively. A key point in ATB is to discover and cultivate the
                            witness attitude: A positioning of our attention that watches and witnesses all the
                            movements in our being, without judging, analyzing or getting caught by any of them.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <livewire:hello-world />
</x-layout>