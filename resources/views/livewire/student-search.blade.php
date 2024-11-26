<div class="p-6 max-h-[80vh] overflow-y-auto relative">
    @if($message)
    <div
        class="mb-4 p-4 rounded {{ str_contains($message, 'successfully') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
        {{ $message }}
    </div>
    @endif

    <label for="search" class="block text-gray-700 font-bold mb-2">Select Student</label>
    <div class="relative" x-data="{ searchInput: '' }">
        <input type="text" id="search" wire:model.live="search" x-model.debounce="searchInput"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none"
            placeholder="Start typing student name..." autocomplete="off">

        @if($showDropdown && $students->isNotEmpty())
        <div class="absolute z-[60] w-full mt-1 bg-white rounded-md shadow-lg overflow-y-auto"
            style="max-height: calc(80vh - 250px);">
            @foreach($students as $student)
            <div wire:key="student-{{ $student->id }}"
                wire:click="selectStudent({{ $student->id }}, '{{ $student->first_name }}', '{{ $student->last_name }}')"
                x-on:click="searchInput = '{{ $student->first_name }} {{ $student->last_name }}'"
                class="cursor-pointer p-2 hover:bg-gray-100 transition duration-150">
                {{ $student->first_name }} {{ $student->last_name }}
            </div>
            @endforeach
        </div>
        @endif

        @if($selectedId)
        <button type="button" wire:click="clearSelection"
            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
            ×
        </button>
        @endif
    </div>


    <div class="mt-4 flex items-center space-x-3">
        <button type="button" wire:click="addParticipant"
            class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
            @if(!$selectedId) disabled @endif>
            Add Participant
        </button>
        <button type="button" @click="$dispatch('close-modal', 'add-participant')"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition duration-150">
            Cancel
        </button>
    </div>
</div>