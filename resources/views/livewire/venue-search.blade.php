<div class="relative">
    <label for="venue_search" class="block text-gray-700 font-bold mb-2">Venue</label>
    <div class="relative">
        <input type="text" id="venue_search" wire:model.live="search"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
            placeholder="Search venue..." autocomplete="off">
        <input type="hidden" name="venue_id" wire:model="selectedVenueId">

        @if($showDropdown && !empty($venues))
        <div class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-y-auto">
            @foreach($venues as $venue)
            <div wire:key="{{ $venue->id }}" wire:click="selectVenue('{{ $venue->id }}', '{{ $venue->venue_name }}')"
                class="cursor-pointer p-2 hover:bg-gray-100">
                {{ $venue->venue_name }}
            </div>
            @endforeach
        </div>
        @endif
    </div>

    @error('venue_id')
    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
    @enderror
</div>