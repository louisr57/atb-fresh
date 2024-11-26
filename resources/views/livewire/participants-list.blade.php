<div>
    @if($registrations->isNotEmpty())
    <div class="mt-8">
        <h2 class="text-2xl font-bold mb-4">Participants</h2>
        <table class="min-w-full table-auto border-collapse border border-gray-500">
            <thead class="bg-gray-200 text-left">
                <tr>
                    <th class="border px-4 py-2">Name</th>
                    <th class="border px-4 py-2">Email</th>
                    <th class="border px-4 py-2">Status</th>
                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registrations as $registration)
                <tr class="hover:bg-gray-100">
                    <td class="border px-4 py-2">
                        {{ $registration->student->first_name }} {{ $registration->student->last_name }}
                    </td>
                    <td class="border px-4 py-2">{{ $registration->student->email }}</td>
                    <td class="border px-4 py-2">{{ $registration->end_status }}</td>
                    <td class="border px-4 py-2">
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('registrations.show', $registration->id) }}"
                                class="text-blue-600 hover:underline">View</a>
                            <button type="button" wire:click="deleteRegistration({{ $registration->id }})"
                                wire:confirm="Are you sure you want to remove this participant from the event?"
                                wire:loading.attr="disabled" wire:target="deleteRegistration({{ $registration->id }})"
                                class="text-red-600 hover:text-red-800 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none">
                                <span wire:loading.remove wire:target="deleteRegistration({{ $registration->id }})">
                                    Delete
                                </span>
                                <span wire:loading wire:target="deleteRegistration({{ $registration->id }})"
                                    class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-red-600"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Deleting...
                                </span>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-lg text-gray-600 mt-4">No participants registered yet.</p>
    @endif
</div>