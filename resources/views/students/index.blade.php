@props(['direction' => 'asc'])

<x-layout>
    <x-slot:heading>
        ATB Students
    </x-slot:heading>

    <div class="container mx-auto p-4">

        <!-- Flash Message Section -->
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            {{ session('success') }}
        </div>
        @endif

        {{-- <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold mb-0">Students List</h1>
            <a href="{{ route('students.create') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Create New Student
            </a>
        </div> --}}
        <livewire:students-index-search />
    </div>

    <script>
        // Ensure DOM is fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Get the highlight parameter from the query string
            const urlParams = new URLSearchParams(window.location.search);
            const highlightId = urlParams.get('highlight');

            if (highlightId) {
                // Find the row that matches the student ID
                const row = document.querySelector(`tr[data-student-id="${highlightId}"]`);
                if (row) {
                    // Add a highlight class to the row
                    row.classList.add('bg-yellow-500');

                    // Scroll the row into view smoothly after a small delay to ensure full rendering
                    setTimeout(() => {
                        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 500);

                    // Remove the highlight when the user clicks anywhere on the page
                    document.addEventListener('click', function() {
                        row.classList.remove('bg-yellow-500');
                    }, { once: true });
                }
            }
        });
    </script>

</x-layout>
