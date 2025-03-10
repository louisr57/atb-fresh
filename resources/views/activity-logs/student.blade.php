<x-layout>
    <x-slot:heading>
        Activity Log for {{ $student->first_name }} {{ $student->last_name }}
    </x-slot:heading>

    <div class="container mx-auto p-4">
        <div class="mb-4 flex justify-between items-center">
            <a href="{{ route('students.show', $student) }}" class="text-blue-600 hover:underline">
                ← Back to Student
            </a>
        </div>

        <div class="bg-slate-400 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <!-- Activity Log Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse border border-gray-500">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="border border-gray-500 px-4 py-2 text-left">Date/Time</th>
                            <th class="border border-gray-500 px-4 py-2 text-left">User</th>
                            <th class="border border-gray-500 px-4 py-2 text-left">Action</th>
                            <th class="border border-gray-500 px-4 py-2 text-left">Changes</th>
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
                                    @if($activity->properties->has('attributes'))
                                        <strong>Changed Values:</strong>
                                        <pre class="text-xs mt-1 bg-white p-2 rounded">{{ json_encode($activity->properties['attributes'], JSON_PRETTY_PRINT) }}</pre>
                                    @endif
                                    @if($activity->properties->has('old') && $activity->properties->has('attributes'))
                                        <button onclick="toggleDiff(this)" class="text-blue-600 hover:text-blue-800 text-sm mt-1">
                                            Show Changes
                                        </button>
                                        <div class="hidden mt-2">
                                            @php
                                                $changes = [];
                                                $old = $activity->properties['old'];
                                                $new = $activity->properties['attributes'];
                                                foreach ($new as $key => $value) {
                                                    if (isset($old[$key]) && $old[$key] !== $value) {
                                                        $changes[$key] = [
                                                            'old' => $old[$key],
                                                            'new' => $value
                                                        ];
                                                    }
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
