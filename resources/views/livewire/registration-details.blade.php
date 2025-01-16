<tbody wire:key="{{ $registration->id }}">
    <tr>
        <th class="w-1/5 border px-4 py-2 text-left">Registration Status</th>
        <td class="border px-4 py-2">
            @if (!$isEditing)
                <span>{{ $registration->end_status }}</span>
            @else
                <select wire:model="end_status" class="w-full p-1 border rounded">
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            @endif
        </td>
    </tr>
    <tr>
        <th class="w-1/5 border px-4 py-2 text-left">Registration Date</th>
        <td class="border px-4 py-2">
            {{ $registration->created_at->format('F j, Y') }}
        </td>
    </tr>
    <tr class="bg-gray-100">
        <th class="w-1/5 border px-4 py-2 text-left">Remarks</th>
        <td class="border px-4 py-2">
            @if (!$isEditing)
                <span>{{ $registration->comments ?? 'No remarks' }}</span>
            @else
                <textarea
                    wire:model="comments"
                    rows="3"
                    class="w-full p-1 border rounded"
                    placeholder="Add remarks here..."
                ></textarea>
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2" class="border px-4 py-2">
            <div class="flex justify-between items-center">
                <div>
                    @if (!$isEditing)
                        <button
                            wire:click="startEditing"
                            type="button"
                            class="bg-blue-500 mt-4 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        >
                            Edit Status and Remarks
                        </button>
                    @else
                        <button
                            wire:click="save"
                            type="button"
                            class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm"
                        >
                            Save Changes
                        </button>
                        <button
                            wire:click="cancelEdit"
                            type="button"
                            class="ml-2 bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 text-sm"
                        >
                            Cancel
                        </button>
                    @endif
                </div>
                <div class="flex items-center space-x-12">
                    @if($registration->previousInEvent())
                        <a href="{{ route('registrations.show', $registration->previousInEvent()) }}" class="flex items-center text-gray-600 hover:text-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span class="ml-2">Previous</span>
                        </a>
                    @else
                        <span class="flex items-center text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span class="ml-2">Previous</span>
                        </span>
                    @endif

                    @if($registration->nextInEvent())
                        <a href="{{ route('registrations.show', $registration->nextInEvent()) }}" class="flex items-center text-gray-600 hover:text-gray-900">
                            <span class="mr-2">Next</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <span class="flex items-center text-gray-300">
                            <span class="mr-2">Next</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        </td>
    </tr>
</tbody>
