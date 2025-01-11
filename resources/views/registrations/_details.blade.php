<tbody data-registration-id="{{ $registration->id }}">
    <tr>
        <th class="border px-4 py-2">Registration Status</th>
        <td class="border px-4 py-2">
            <span class="registration-display">{{ $registration->end_status }}</span>
            <div class="registration-edit hidden">
                <select name="end_status" class="w-full p-1 border rounded">
                    @foreach (['Registered', 'Withdrawn', 'Completed', 'Incomplete'] as $status)
                        <option value="{{ $status }}" {{ $registration->end_status == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>
        </td>
    </tr>
    <tr class="bg-gray-100">
        <th class="border px-4 py-2">Remarks</th>
        <td class="border px-4 py-2">
            <span class="registration-display">{{ $registration->comments ?? 'No remarks' }}</span>
            <div class="registration-edit hidden">
                <textarea
                    name="comments"
                    rows="3"
                    class="w-full p-1 border rounded"
                    placeholder="Add remarks here..."
                >{{ $registration->comments }}</textarea>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="border px-4 py-2 text-right">
            <div class="registration-display">
                <button
                    type="button"
                    class="edit-registration bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm"
                >
                    Edit
                </button>
            </div>
            <div class="registration-edit hidden">
                <button
                    type="button"
                    class="save-registration bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm"
                >
                    Save Changes
                </button>
                <button
                    type="button"
                    class="cancel-edit ml-2 bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 text-sm"
                >
                    Cancel
                </button>
            </div>
        </td>
    </tr>
</tbody>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const registrationDetails = document.querySelector('[data-registration-id="{{ $registration->id }}"]');

    // Edit button click
    registrationDetails.querySelector('.edit-registration').addEventListener('click', function() {
        registrationDetails.querySelectorAll('.registration-display').forEach(el => el.classList.add('hidden'));
        registrationDetails.querySelectorAll('.registration-edit').forEach(el => el.classList.remove('hidden'));
    });

    // Cancel button click
    registrationDetails.querySelector('.cancel-edit').addEventListener('click', function() {
        registrationDetails.querySelectorAll('.registration-edit').forEach(el => el.classList.add('hidden'));
        registrationDetails.querySelectorAll('.registration-display').forEach(el => el.classList.remove('hidden'));
    });

    // Save button click
    registrationDetails.querySelector('.save-registration').addEventListener('click', function() {
        const data = {
            end_status: registrationDetails.querySelector('select[name="end_status"]').value,
            comments: registrationDetails.querySelector('textarea[name="comments"]').value
        };

        // Add debug logging
        console.log('Save button clicked');
        console.log('Data to send:', data);

        // Get CSRF token
        const token = document.querySelector('meta[name="csrf-token"]');
        if (!token) {
            console.error('CSRF token not found');
            alert('CSRF token not found. Please refresh the page.');
            return;
        }

        // Show loading state
        const saveButton = registrationDetails.querySelector('.save-registration');
        const originalText = saveButton.textContent;
        saveButton.textContent = 'Saving...';
        saveButton.disabled = true;

        fetch(`{{ route('registrations.update', $registration) }}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token.content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response:', data);
            if (data.success) {
                // Update the display values
                const statusDisplay = registrationDetails.querySelector('.registration-display:first-child');
                const commentsDisplay = registrationDetails.querySelectorAll('.registration-display')[1];

                console.log('Updating status to:', data.registration.end_status);
                console.log('Updating comments to:', data.registration.comments);

                statusDisplay.textContent = data.registration.end_status;
                commentsDisplay.textContent = data.registration.comments || 'No remarks';

                // Switch back to display mode
                registrationDetails.querySelectorAll('.registration-edit').forEach(el => el.classList.add('hidden'));
                registrationDetails.querySelectorAll('.registration-display').forEach(el => el.classList.remove('hidden'));
            } else {
                throw new Error('Update was not successful');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving changes: ' + error.message);
        })
        .finally(() => {
            // Reset button state
            saveButton.textContent = originalText;
            saveButton.disabled = false;
        });
    });
});
</script>
@endpush
