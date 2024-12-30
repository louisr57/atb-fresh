<x-layout>
    <x-slot:heading>
        Create New Event
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Create New Event</h1>

        @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 mb-4 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <form action="{{ route('events.store') }}" method="POST">
                @csrf

                <!-- First row - Course, Facilitator, Venue selections -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Course Selection -->
                    <div class="mb-4" x-data="{ open: false, selectedId: '', selectedText: '', eventTitle: '' }">
                        <label for="course_id" class="block text-gray-700 font-bold mb-2">Course Title</label>
                        <div class="relative">
                            <input type="text" x-model="selectedText" @click="open = true" @focus="open = true" class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200
                                      @error('course_id') border-red-500 @enderror" placeholder="Select Course"
                                readonly>
                            <input type="hidden" name="course_id" :value="selectedId">
                            <input type="hidden" name="title" :value="eventTitle">
                            @error('course_id')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                            <div x-show="open" @click.away="open = false"
                                class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-y-auto">
                                @foreach($courses as $course)
                                <div class="cursor-pointer p-2 hover:bg-gray-100" @click="selectedId = '{{ $course->id }}';
                                           selectedText = '{{ $course->course_title }}';
                                           eventTitle = '{{ $course->course_title }}';
                                           open = false">
                                    {{ $course->course_title }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Facilitator Selection -->
                    <div class="mb-4" x-data="{
                        open: false,
                        selectedFacilitators: [],
                        toggleFacilitator(id, name) {
                            const index = this.selectedFacilitators.findIndex(f => f.id === id);
                            if (index === -1) {
                                this.selectedFacilitators.push({ id: id, name: name });
                            } else {
                                this.selectedFacilitators.splice(index, 1);
                            }
                        },
                        isSelected(id) {
                            return this.selectedFacilitators.some(f => f.id === id);
                        }
                    }">
                        <label class="block text-gray-700 font-bold mb-0">Facilitators</label>
                        <div class="relative">
                            <!-- Selected facilitators display -->
                            <div class="mb-2 flex flex-wrap gap-2">
                                <template x-for="facilitator in selectedFacilitators" :key="facilitator.id">
                                    <div class="bg-blue-100 text-blue-800 px-2 py-1 rounded flex items-center">
                                        <span x-text="facilitator.name"></span>
                                        <button type="button" @click="toggleFacilitator(facilitator.id, facilitator.name)"
                                                class="ml-2 text-blue-600 hover:text-blue-800">&times;</button>
                                    </div>
                                </template>
                            </div>

                            <!-- Dropdown trigger -->
                            <button type="button"
                                    @click="open = !open"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200
                                           @error('facilitator_ids') border-red-500 @enderror">
                                Select Facilitators
                            </button>

                            <!-- Hidden inputs for form submission -->
                            <template x-for="facilitator in selectedFacilitators" :key="facilitator.id">
                                <input type="hidden" name="facilitator_ids[]" :value="facilitator.id">
                            </template>

                            @error('facilitator_ids')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror

                            <!-- Dropdown menu -->
                            <div x-show="open" @click.away="open = false"
                                class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-y-auto">
                                @foreach($facilitators as $facilitator)
                                <div class="cursor-pointer p-2 hover:bg-gray-100 flex items-center"
                                     @click="toggleFacilitator('{{ $facilitator->id }}', '{{ $facilitator->first_name }} {{ $facilitator->last_name }}')">
                                    <div class="flex-1">{{ $facilitator->first_name }} {{ $facilitator->last_name }}</div>
                                    <div x-show="isSelected('{{ $facilitator->id }}')" class="text-blue-600">✓</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Venue Selection -->
                    <div class="mb-4" x-data="{ open: false, selectedId: '', selectedText: '' }">
                        <label for="venue_id" class="block text-gray-700 font-bold mb-2">Venue</label>
                        <div class="relative">
                            <input type="text" x-model="selectedText" @click="open = true" @focus="open = true" class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200
                                      @error('venue_id') border-red-500 @enderror" placeholder="Select Venue" readonly>
                            <input type="hidden" name="venue_id" :value="selectedId">
                            @error('venue_id')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                            <div x-show="open" @click.away="open = false"
                                class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-y-auto">
                                @foreach($venues as $venue)
                                <div class="cursor-pointer p-2 hover:bg-gray-100" @click="selectedId = '{{ $venue->id }}';
                                           selectedText = '{{ $venue->venue_name }}';
                                           open = false">
                                    {{ $venue->venue_name }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second row - Date fields -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Date From -->
                    <div class="mb-4">
                        <label for="datefrom" class="block text-gray-700 font-bold mb-2">Date From</label>
                        <input type="text" id="datefrom" name="datefrom"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            required readonly autocomplete="off">
                    </div>

                    <!-- Date To -->
                    <div class="mb-4">
                        <label for="dateto" class="block text-gray-700 font-bold mb-2">Date To</label>
                        <input type="text" id="dateto" name="dateto"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                            required readonly autocomplete="off">
                    </div>
                </div>

                <!-- Third row - Time fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Time From -->
                    <div class="mb-4" x-data="{ open: false, selectedTime: '' }">
                        <label for="timefrom" class="block text-gray-700 font-bold mb-2">Time From</label>
                        <div class="relative">
                            <input type="text" x-model="selectedTime" @click="open = true"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                                name="timefrom" readonly>
                            <div x-show="open" @click.away="open = false" class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-y-auto">
                                @for ($hour = 6; $hour <= 22; $hour++)
                                    @foreach(['00', '30'] as $minute)
                                        <div class="cursor-pointer p-2 hover:bg-gray-100" @click="selectedTime = '{{ sprintf('%02d', $hour) }}:{{ $minute }}'; open = false"> {{ sprintf('%02d', $hour) }}:{{ $minute }} </div>
                                    @endforeach
                                @endfor
                            </div>
                    </div>
                </div>

                    <!-- Time To -->
                    <div class="mb-4" x-data="{ open: false, selectedTime: '' }">
                        <label for="timeto" class="block text-gray-700 font-bold mb-2">Time To</label>
                        <div class="relative">
                            <input type="text" x-model="selectedTime" @click="open = true"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200"
                                name="timeto" readonly>
                            <div x-show="open" @click.away="open = false"
                                class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-y-auto">
                                @for ($hour = 6; $hour <= 22; $hour++)
                                    @foreach(['00', '30'] as $minute)
                                        <div class="cursor-pointer p-2 hover:bg-gray-100"
                                            @click="selectedTime = '{{ sprintf('%02d', $hour) }}:{{ $minute }}'; open = false">
                                            {{ sprintf('%02d', $hour) }}:{{ $minute }}
                                        </div>
                                    @endforeach
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Remarks - Full width -->
                <div class="mb-4">
                    <label for="remarks" class="block text-gray-700 font-bold mb-2">Remarks</label>
                    <textarea id="remarks" name="remarks"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-slate-800 bg-gray-200 h-32">{{ old('remarks') }}</textarea>
                </div>

                <!-- Submit and Cancel buttons -->
                <div class="flex justify-between items-center mt-6">
                    <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                        Create Event
                    </button>
                    <a href="{{ route('events.index') }}" class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Include Pikaday CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ['datefrom', 'dateto'].forEach(function(fieldId) {
                new Pikaday({
                    field: document.getElementById(fieldId),
                    format: 'YYYY-MM-DD',
                    showYearDropdown: true,
                    showMonthDropdown: true,
                    toString(date, format) {
                        date.setHours(12, 0, 0, 0);
                        return date ? date.toISOString().split('T')[0] : '';
                    },
                    parse(dateString, format) {
                        const parts = dateString.split('-');
                        return dateString ? new Date(parts[0], parts[1] - 1, parts[2]) : null;
                    },
                    onSelect: function(date) {
                        date.setHours(12, 0, 0, 0);
                        this._field.value = date.toISOString().split('T')[0];
                    }
                });
            });
        });
    </script>
</x-layout>
