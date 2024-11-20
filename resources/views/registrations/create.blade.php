{{-- resources/views/registrations/create.blade.php --}}
<x-layout>
    <x-slot:heading>
        Add Participant to Event
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @livewire('student-search', ['event' => $event])
        </div>
    </div>
</x-layout>

{{--
<livewire:student-search :eventId="$event->id" /> --}}