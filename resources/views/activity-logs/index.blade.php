<x-layout>
    <x-slot:heading>
        Activity Logs
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <!-- Filters -->
        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <form onsubmit="return handleFilterSubmit(event)" class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Model Type</label>
                        <select name="model" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">All Models</option>
                            <option value="student" {{ request('model') == 'student' ? 'selected' : '' }}>Students</option>
                            <option value="course" {{ request('model') == 'course' ? 'selected' : '' }}>Courses</option>
                            <option value="event" {{ request('model') == 'event' ? 'selected' : '' }}>Events</option>
                            <option value="facilitator" {{ request('model') == 'facilitator' ? 'selected' : '' }}>Facilitators</option>
                            <option value="registration" {{ request('model') == 'registration' ? 'selected' : '' }}>Registrations</option>
                            <option value="venue" {{ request('model') == 'venue' ? 'selected' : '' }}>Venues</option>
                        </select>
                    </div>
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
                        <select name="user" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">All Users</option>
                            <option value="System" {{ request('user') == 'System' ? 'selected' : '' }}>System</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                <option value="{{ $user->name }}" {{ request('user') == $user->name ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
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
                    <div class="flex items-end gap-2">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Filter
                        </button>
                        <a href="{{ route('activity-logs.index') }}" class="bg-teal-700 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Reset
                        </a>
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
                            <tr
                                class="hover:bg-sky-100 cursor-pointer"
                                data-model="{{ strtolower(class_basename($activity->subject_type)) }}"
                                data-subject-id="{{ $activity->subject_id }}"
                                onclick="handleRowClick(this)"
                            >
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
                                    @switch($activity->subject_type)
                                        @case(\App\Models\Student::class)
                                            <strong>Student:</strong>
                                            @if($activity->subject)
                                                <a href="{{ route('students.show', $activity->subject_id) }}" class="text-blue-600 hover:underline">
                                                    {{ $activity->subject->first_name }} {{ $activity->subject->last_name }}
                                                </a>
                                                @if(str_contains($activity->description, 'viewed'))
                                                    <span class="text-gray-500">
                                                        Profile viewed by {{ $activity->causer->name ?? 'System' }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-500">(Student no longer exists)</span>
                                            @endif
                                            <br>
                                            @break

                                        @case(\App\Models\Course::class)
                                            <strong>Course:</strong>
                                            @if($activity->subject)
                                                <a href="{{ route('courses.show', $activity->subject_id) }}" class="text-blue-600 hover:underline">
                                                    {{ $activity->subject->course_title }}
                                                </a>
                                                @if(str_contains($activity->description, 'viewed'))
                                                    <span class="text-gray-500">
                                                        Profile viewed by {{ $activity->causer->name ?? 'System' }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-500">(Course no longer exists)</span>
                                            @endif
                                            <br>
                                            @break

                                        @case(\App\Models\Event::class)
                                            <strong>Event:</strong>
                                            @if($activity->subject)
                                                <a href="{{ route('events.show', $activity->subject_id) }}" class="text-blue-600 hover:underline">
                                                    {{ $activity->subject->title }}
                                                </a>
                                                @if(str_contains($activity->description, 'viewed'))
                                                    <span class="text-gray-500">
                                                        Profile viewed by {{ $activity->causer->name ?? 'System' }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-500">(Event no longer exists)</span>
                                            @endif
                                            <br>
                                            @break

                                        @case(\App\Models\Facilitator::class)
                                            <strong>Facilitator:</strong>
                                            @if($activity->subject)
                                                <a href="{{ route('facilitators.show', $activity->subject_id) }}" class="text-blue-600 hover:underline">
                                                    {{ $activity->subject->first_name }} {{ $activity->subject->last_name }}
                                                </a>
                                                @if(str_contains($activity->description, 'viewed'))
                                                    <span class="text-gray-500">
                                                        Profile viewed by {{ $activity->causer->name ?? 'System' }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-500">(Facilitator no longer exists)</span>
                                            @endif
                                            <br>
                                            @break

                                        @case(\App\Models\Registration::class)
                                            <strong>Registration:</strong>
                                            @if($activity->subject)
                                                <a href="{{ route('registrations.show', $activity->subject_id) }}" class="text-blue-600 hover:underline">
                                                    {{ $activity->subject->student->first_name }} {{ $activity->subject->student->last_name }}
                                                    - {{ $activity->subject->event->title }}
                                                </a>
                                                @if(str_contains($activity->description, 'viewed'))
                                                    <span class="text-gray-500">
                                                        Profile viewed by {{ $activity->causer->name ?? 'System' }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-500">(Registration no longer exists)</span>
                                            @endif
                                            <br>
                                            @break

                                        @case(\App\Models\Venue::class)
                                            <strong>Venue:</strong>
                                            @if($activity->subject)
                                                <a href="{{ route('venues.show', $activity->subject_id) }}" class="text-blue-600 hover:underline">
                                                    {{ $activity->subject->venue_name }}
                                                </a>
                                                @if(str_contains($activity->description, 'viewed'))
                                                    <span class="text-gray-500">
                                                        Profile viewed by {{ $activity->causer->name ?? 'System' }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-500">(Venue no longer exists)</span>
                                            @endif
                                            <br>
                                            @break
                                    @endswitch

                                    @if(str_contains($activity->description, 'deleted'))
                                        @php
                                            // Define field orders for each model
                                            $fieldOrders = [
                                                \App\Models\Student::class => [
                                                    'first_name', 'last_name', 'email', 'phone_number',
                                                    'dob', 'gender', 'address', 'city', 'state',
                                                    'country', 'post_code', 'website', 'ident',
                                                    'next_of_kin', 'allergies', 'special_needs', 'reg_count'
                                                ],
                                                \App\Models\Course::class => [
                                                    'course_code', 'course_title', 'description',
                                                    'prerequisites', 'duration'
                                                ],
                                                \App\Models\Event::class => [
                                                    'title', 'datefrom', 'dateto', 'timefrom', 'timeto',
                                                    'venue_id', 'course_id', 'facilitator_id', 'remarks',
                                                    'participant_count'
                                                ],
                                                \App\Models\Facilitator::class => [
                                                    'first_name', 'last_name', 'gender', 'email',
                                                    'phone_number', 'address', 'city', 'state',
                                                    'country', 'post_code', 'website', 'dob'
                                                ],
                                                \App\Models\Registration::class => [
                                                    'student_id', 'event_id', 'end_status', 'comments'
                                                ],
                                                \App\Models\Venue::class => [
                                                    'venue_name', 'address', 'city', 'state', 'country',
                                                    'postcode', 'location_geocode', 'remarks'
                                                ]
                                            ];

                                            // Get the appropriate field order for this model
                                            $fieldOrder = $fieldOrders[$activity->subject_type] ?? [];

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

                                                    // Get the appropriate field order for this model
                                                    $fieldOrder = $fieldOrders[$activity->subject_type] ?? [];

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
                {{ $activities->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function handleFilterSubmit(event) {
            event.preventDefault();

            // Build the URL with all form parameters
            const form = event.target;
            const url = new URL(window.location.href);

            // Get all form inputs
            const formData = new FormData(form);

            // Clear existing parameters
            url.search = '';

            // Add non-empty form values to URL
            for (const [key, value] of formData.entries()) {
                if (value) {
                    url.searchParams.set(key, value);
                }
            }

            // Force pagination refresh by setting page=1
            url.searchParams.set('page', '1');

            // Navigate to the filtered view
            window.location.href = url.toString();
            return false;
        }

        function toggleDiff(button) {
            const diff = button.nextElementSibling;
            diff.classList.toggle('hidden');
            button.textContent = diff.classList.contains('hidden') ? 'Show Changes' : 'Hide Changes';
        }

        function handleRowClick(row) {
            // Don't trigger if clicking a link or button
            if (event.target.closest('a, button')) {
                return;
            }

            const model = row.dataset.model;
            const subjectId = row.dataset.subjectId;

            // Build the URL with the model and subject_id parameters
            const url = new URL(window.location.href);
            url.searchParams.set('model', model);
            url.searchParams.set('subject_id', subjectId);

            // Clear other filters
            url.searchParams.delete('action');
            url.searchParams.delete('user');
            url.searchParams.delete('from_date');
            url.searchParams.delete('to_date');

            // Force pagination refresh
            url.searchParams.set('page', '1');

            // Update model select to show "All Models"
            document.querySelector('select[name="model"]').value = '';

            // Navigate to the filtered view
            window.location.href = url.toString();
        }
    </script>
    @endpush
</x-layout>
