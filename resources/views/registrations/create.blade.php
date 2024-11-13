<x-layout>
    <x-slot:heading>
        Add Participant to Event
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <form action="{{ route('registrations.store') }}" method="POST">
                @csrf
                <input type="hidden" name="event_id" value="{{ $event->id }}">
                <input type="hidden" name="end_status" value="registered">

                <div class="mb-4" x-data="{ open: false, selectedId: '', selectedText: '' }">
                    <label class="block text-gray-700 font-bold mb-2">Select Student</label>
                    <div class="relative">
                        <input type="text" x-model="selectedText" @click="open = true"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            placeholder="Select student" readonly>
                        <input type="hidden" name="student_id" x-model="selectedId">

                        <div x-show="open" @click.away="open = false"
                            class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-y-auto">
                            @foreach($students as $student)
                            <div class="cursor-pointer p-2 hover:bg-gray-100" @click="selectedId = '{{ $student->id }}';
                                        selectedText = '{{ $student->first_name }} {{ $student->last_name }}';
                                        open = false">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add Participant
                    </button>
                    <a href="{{ route('events.show', $event->id) }}"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout>