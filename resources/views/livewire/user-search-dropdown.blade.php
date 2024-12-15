<div class="relative">
    <input type="text"
        wire:model.live="search"
        wire:keydown.escape="$set('showDropdown', false)"
        wire:keydown.tab="$set('showDropdown', false)"
        class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        placeholder="Search by user name"
        autocomplete="off"
    >
    <input type="hidden" name="user" value="{{ $selectedUser }}">

    @if($showDropdown && !empty($users))
        <div class="absolute z-10 w-full bg-white rounded-b border-l border-r border-b max-h-48 overflow-y-auto">
            @foreach($users as $user)
                <div wire:click="selectUser('{{ $user->id }}', '{{ $user->name }}')"
                     class="p-2 hover:bg-sky-100 cursor-pointer">
                    {{ $user->name }}
                </div>
            @endforeach
        </div>
    @endif

    @if($selectedUser)
        <button type="button"
                wire:click="clearSelection"
                class="absolute right-3 top-2.5 text-gray-500 hover:text-gray-700">
            ×
        </button>
    @endif
</div>
