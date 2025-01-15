<x-layout>
    <x-slot:heading>
        Event Details
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <!-- Flash Message Component -->
        @livewire('flash-message')

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">{{ $event->title }}</h1>
            <div class="space-x-4">
                @if($event->isEmpty())
                    <form action="{{ route('events.destroy', $event->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                            onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.');">
                            Delete Event
                        </button>
                    </form>
                @endif
                <button type="button" x-data @click="$dispatch('open-modal', 'add-participant')"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Add Participant
                </button>
                <a href="{{ route('events.edit', $event->id) }}"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Event
                </a>
            </div>
        </div>

        <!-- Event Details -->
        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h1 class="text-3xl font-bold mb-4">{{ $event->course->course_title }}</h1>

            <p class="text-lg mb-2">
                <strong>Date:</strong> {{ $event->datefrom }} to {{ $event->dateto ?? 'N/A' }}
            </p>
            <div class="text-lg mb-2">
                <strong>Facilitators:</strong>
                <ul class="list-disc ml-6">
                    @foreach($event->facilitators as $facilitator)
                        <li>{{ $facilitator->first_name }} {{ $facilitator->last_name }}</li>
                    @endforeach
                </ul>
            </div>
            <p class="text-lg mb-1">
                @livewire('participant-count', ['event' => $event])
            </p>
        </div>

        <!-- Participants List -->
        @livewire('participants-list', ['event' => $event])
    </div>

    <!-- Add Participant Modal -->
    <x-modal name="add-participant" :show="false" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Add Participant to Event
            </h2>
            @livewire('student-search', ['event' => $event, 'searchInput' => ''])
        </div>
    </x-modal>
</x-layout>
