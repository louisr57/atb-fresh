<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    <input
        type="text"
        wire:model.live="search"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        @focus="open = true"
        @click.away="open = false"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
    >

    <!-- Dropdown -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg"
         style="display: none;">
        <ul class="py-1 overflow-auto text-base leading-6 rounded-md shadow-xs max-h-60 focus:outline-none sm:text-sm sm:leading-5">
            @foreach ($suggestions as $suggestion)
                <li wire:key="{{ $loop->index }}"
                    wire:click="selectSuggestion('{{ $suggestion }}')"
                    @click="open = false"
                    class="relative py-2 pl-3 text-gray-900 cursor-pointer select-none hover:bg-blue-50">
                    {{ $suggestion }}
                </li>
            @endforeach
        </ul>
    </div>
</div>
