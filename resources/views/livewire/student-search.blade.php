{{-- resources/views/livewire/student-search.blade.php --}}
<div class="relative" x-data="{ open: false }">
    <label class="block text-gray-700 font-bold mb-2">Select Student</label>
    <input type="text" wire:model.live="search" @click="open = true" @click.away="open = false"
        class="w-full rounded border px-3 py-2" placeholder="Search student name..." value="{{ $selectedStudentName }}">

    @if (strlen($search) >= 2 && !$selectedStudentId)
    <div x-show="open" class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-y-auto">
        @forelse ($students as $student)
        <div class="cursor-pointer p-2 hover:bg-gray-100"
            wire:click="selectStudent({{ $student->id }}); $nextTick(() => { open = false })">
            {{ $student->first_name }} {{ $student->last_name }}
        </div>
        @empty
        <div class="p-2 text-gray-500">No students found</div>
        @endforelse
    </div>
    @endif

    @if($selectedStudentId)
    <button type="button" wire:click="addParticipant"
        class="mt-3 bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
        Add Participant
    </button>
    @endif
</div>