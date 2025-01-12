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
        <td colspan="2" class="border px-4 py-2">
            <div class="flex justify-between items-center">
                <div>
                    <div class="registration-display">
                        <button
                            type="button"
                            class="edit-registration bg-blue-500 mt-4 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        >
                            Edit Status and Remarks
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
    registrationDetails.querySelector('.save-registration').addEventListener('click', async function() {
        const data = {
            end_status: registrationDetails.querySelector('select[name="end_status"]').value,
            comments: registrationDetails.querySelector('textarea[name="comments"]').value
        };

        // Add debug logging
        console.log('Save button clicked');
        console.log('Data to send:', data);

        // Get CSRF token
        function getCsrfToken() {
            return new Promise((resolve, reject) => {
                // First try to get the token from the meta tag
                const metaToken = document.querySelector('meta[name="csrf-token"]');
                if (metaToken && metaToken.content) {
                    return resolve(metaToken.content);
                }

                // If meta tag is not found, try to get it from a form
                const tokenInput = document.querySelector('input[name="_token"]');
                if (tokenInput && tokenInput.value) {
                    return resolve(tokenInput.value);
                }

                // If neither is found, reject with a clear error
                reject(new Error('CSRF token not found. Please ensure you are logged in and try refreshing the page.'));
            });
        }

        try {
            // Show loading state
            const saveButton = registrationDetails.querySelector('.save-registration');
            const originalText = saveButton.textContent;
            saveButton.textContent = 'Saving...';
            saveButton.disabled = true;

            const token = await getCsrfToken();

            // Add a hidden form with CSRF token for future use
            if (!document.querySelector('input[name="_token"]')) {
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = token;
                document.body.appendChild(tokenInput);
            }

            const response = await fetch(`{{ route('registrations.update', $registration) }}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseData = await response.json();
            console.log('Response:', responseData);

            if (!responseData.success) {
                throw new Error('Update was not successful');
            }

            // Update the display values
            const statusDisplay = registrationDetails.querySelector('.registration-display:first-child');
            const commentsDisplay = registrationDetails.querySelectorAll('.registration-display')[1];

            console.log('Updating status to:', responseData.registration.end_status);
            console.log('Updating comments to:', responseData.registration.comments);

            statusDisplay.textContent = responseData.registration.end_status;
            commentsDisplay.textContent = responseData.registration.comments || 'No remarks';

            // Switch back to display mode
            registrationDetails.querySelectorAll('.registration-edit').forEach(el => el.classList.add('hidden'));
            registrationDetails.querySelectorAll('.registration-display').forEach(el => el.classList.remove('hidden'));
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while saving changes: ' + error.message);
            saveButton.textContent = originalText;
            saveButton.disabled = false;
        }
    });
});
</script>
@endpush
