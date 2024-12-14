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
                                    @if($activity->subject)
                                        <strong>{{ class_basename($activity->subject_type) }}:</strong>
                                        @if($activity->subject_type === \App\Models\Student::class)
                                            <a href="{{ route('students.show', $activity->subject_id) }}" class="text-blue-600 hover:underline">
                                                {{ $activity->subject->first_name }} {{ $activity->subject->last_name }}
                                            </a>
                                        @endif
                                        <br>
                                    @endif
                                    @if($activity->properties->has('attributes'))
                                        <strong>Changed:</strong>
                                        <pre class="text-xs mt-1 bg-white p-2 rounded">{{ json_encode($activity->properties['attributes'], JSON_PRETTY_PRINT) }}</pre>
                                    @endif
                                    @if($activity->properties->has('old') && $activity->properties->has('new'))
                                        <button onclick="toggleDiff(this)" class="text-blue-600 hover:text-blue-800 text-sm mt-1">
                                            Show Changes
                                        </button>
                                        <div class="hidden mt-2">
                                            <strong>Before:</strong>
                                            <pre class="text-xs mt-1 bg-white p-2 rounded">{{ json_encode($activity->properties['old'], JSON_PRETTY_PRINT) }}</pre>
                                            <strong>After:</strong>
                                            <pre class="text-xs mt-1 bg-white p-2 rounded">{{ json_encode($activity->properties['new'], JSON_PRETTY_PRINT) }}</pre>
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
