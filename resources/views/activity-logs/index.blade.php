<x-layout>
    <x-slot:heading>
        Activity Logs
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <!-- Filters -->
        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <form action="{{ route('activity-logs.index') }}" method="GET" class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Action Type</label>
                        <select name="action" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">All Actions</option>
                            <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                            <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                            <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                            <option value="viewed" {{ request('action') == 'viewed' ? 'selected' : '' }}>Viewed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">User</label>
                        <livewire:user-search-dropdown />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">From Date</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">To Date</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Filter
                        </button>
                    </div>
                </div>
            </form>

            <!-- Activity Log Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse border border-gray-500">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="border border-gray-500 px-4 py-2 text-left">Date/Time</th>
                            <th class="border border-gray-500 px-4 py-2 text-left">User</th>
                            <th class="border border-gray-500 px-4 py-2 text-left">Action</th>
                            <th class="border border-gray-500 px-4 py-2 text-left">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-50">
                        @foreach($activities as $activity)
                            <tr class="hover:bg-sky-100">
                                <td class="border border-gray-500 px-4 py-2">
                                    {{ $activity->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="border border-gray-500 px-4 py-2">
                                    {{ $activity->causer->name ?? 'System' }}
                                </td>
                                <td class="border border-gray-500 px-4 py-2">
                                    {{ $activity->description }}
                                </td>
                                <td class="border border-gray-500 px-4 py-2">
                                    {{-- @dump($activity->description); --}}
                                    @if($activity->subject_type === \App\Models\Student::class)

                                        <strong>Student:</strong>
                                        @if($activity->subject)
                                            <a href="{{ route('students.show', $activity->subject_id) }}" class="text-blue-600 hover:underline">
                                                {{ $activity->subject->first_name }} {{ $activity->subject->last_name }}
                                            </a>
                                            {{-- @dump($activity->description); --}}
                                            @if(str_contains($activity->description, 'viewed'))
                                                <span class="text-gray-500">
                                                    Profile viewed by {{ $activity->causer->name ?? 'System' }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-gray-500">(Student no longer exists)</span>
                                        @endif
                                        <br>
                                    @endif

                                    @if(str_contains($activity->description, 'deleted'))
                                        @php
                                            // Define the desired field order (same as for updates)
                                            $fieldOrder = [
                                                'first_name',
                                                'last_name',
                                                'email',
                                                'phone_number',
                                                'dob',
                                                'gender',
                                                'address',
                                                'city',
                                                'state',
                                                'country',
                                                'post_code',
                                                'website',
                                                'ident',
                                                'next_of_kin',
                                                'allergies',
                                                'special_needs'
                                            ];

                                            // Get the properties
                                            $properties = $activity->properties;
                                            $attributes = $properties['old'] ?? [];
                                                // dd($attributes);
                                            // Create ordered array for the properties we want to show
                                            $orderedProperties = [];

                                            // First add properties in our desired order
                                            foreach ($fieldOrder as $field) {
                                                // dd(isset($attributes[$field]));
                                                if (isset($attributes[$field])) {
                                                    $orderedProperties[$field] = $attributes[$field];
                                                }
                                            }

                                            // Then add any remaining properties that weren't in our order
                                            foreach ($attributes as $key => $value) {
                                                if (!isset($orderedProperties[$key])) {
                                                    $orderedProperties[$key] = $value;
                                                }
                                            }
                                            // dd($orderedProperties);
                                        @endphp
                                        <pre class="text-xs mt-1 bg-white p-2 rounded">{{ json_encode($orderedProperties, JSON_PRETTY_PRINT) }}</pre>
                                    @endif

                                    @if(str_contains($activity->description, 'updated'))
                                        <button onclick="toggleDiff(this)" class="text-blue-600 hover:text-blue-800 text-sm mt-1">
                                            Show Changes
                                        </button>
                                        <div class="hidden mt-2">
                                            @php
                                                $changes = [];
                                                $properties = $activity->properties;

                                                if (isset($properties['old']) && isset($properties['attributes'])) {
                                                    $old = $properties['old'];
                                                    $new = $properties['attributes'];

                                                    // Track all changed fields
                                                    $allFields = array_unique(array_merge(array_keys($old), array_keys($new)));
                                                    foreach ($allFields as $key) {
                                                        $oldValue = $old[$key] ?? null;
                                                        $newValue = $new[$key] ?? null;

                                                        // Include field if values are different or if one value doesn't exist
                                                        if ($oldValue !== $newValue) {
                                                            $changes[$key] = [
                                                                'old' => $oldValue ?? '(empty)',
                                                                'new' => $newValue ?? '(empty)'
                                                            ];
                                                        }
                                                    }

                                                    // Define the desired field order
                                                    $fieldOrder = [
                                                        'first_name',
                                                        'last_name',
                                                        'email',
                                                        'phone_number',
                                                        'dob',
                                                        'gender',
                                                        'address',
                                                        'city',
                                                        'state',
                                                        'country',
                                                        'post_code',
                                                        'website',
                                                        'ident',
                                                        'next_of_kin',
                                                        'allergies',
                                                        'special_needs'
                                                    ];

                                                    // Sort the changes array based on the field order
                                                    $sortedChanges = [];
                                                    foreach ($fieldOrder as $field) {
                                                        if (isset($changes[$field])) {
                                                            $sortedChanges[$field] = $changes[$field];
                                                        }
                                                    }
                                                    // Add any fields that weren't in the order array at the end
                                                    foreach ($changes as $field => $value) {
                                                        if (!isset($sortedChanges[$field])) {
                                                            $sortedChanges[$field] = $value;
                                                        }
                                                    }
                                                    $changes = $sortedChanges;
                                                }
                                            @endphp
                                            @if(count($changes) > 0)
                                                @foreach($changes as $field => $values)
                                                    <div class="mb-2">
                                                        <strong>{{ ucwords(str_replace('_', ' ', $field)) }}:</strong>
                                                        <div class="grid grid-cols-2 gap-4">
                                                            <div class="bg-red-50 p-1 rounded">
                                                                <span class="text-red-600">- {{ $values['old'] }}</span>
                                                            </div>
                                                            <div class="bg-green-50 p-1 rounded">
                                                                <span class="text-green-600">+ {{ $values['new'] }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-gray-500">No changes detected</p>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $activities->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleDiff(button) {
            const diff = button.nextElementSibling;
            diff.classList.toggle('hidden');
            button.textContent = diff.classList.contains('hidden') ? 'Show Changes' : 'Hide Changes';
        }
    </script>
    @endpush
</x-layout>
