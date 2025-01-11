<tbody>
    <tr>
        <th class="border px-4 py-2">Registration Status</th>
        <td class="border px-4 py-2">
            @if ($isEditing)
                <select wire:model.live="end_status" class="w-full p-1 border rounded">
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            @else
                {{ ucfirst($end_status) }}
            @endif
        </td>
    </tr>
    <tr class="bg-gray-100">
        <th class="border px-4 py-2">Remarks</th>
        <td class="border px-4 py-2">
            @if ($isEditing)
                <textarea
                    wire:model.live="comments"
                    rows="3"
                    class="w-full p-1 border rounded"
                    placeholder="Add remarks here..."
                ></textarea>
            @else
                {{ $comments ?? 'No remarks' }}
            @endif
        </td>
    </tr>
    <tr class="{{ $isEditing ? 'bg-gray-100' : '' }}">
        <td colspan="2" class="border px-4 py-2 text-right">
            @if ($isEditing)
                <button
                    wire:click="save"
                    type="button"
                    class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm"
                >
                    Save Changes
                </button>
                <button
                    wire:click="toggleEdit"
                    type="button"
                    class="ml-2 bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 text-sm"
                >
                    Cancel
                </button>
            @else
                <button
                    wire:click="toggleEdit"
                    type="button"
                    class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm"
                >
                    Edit
                </button>
            @endif
        </td>
    </tr>
</tbody>
