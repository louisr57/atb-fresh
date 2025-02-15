<?php

namespace App\Livewire;

use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class StudentSearch extends Component
{
    public $event;

    public $search = '';

    public $selectedName = '';

    public $selectedId;

    public $showDropdown = false;

    public $message = '';

    public $selectedStudentName = '';

    public function mount($event)
    {
        $this->event = $event;
        // Clear the search when component is mounted
        $this->dispatch('init-search');
    }

    public function getStudentsProperty()
    {
        if (strlen($this->search) >= 2) {
            $this->showDropdown = true;

            $students = Student::where(function ($query) {
                $query->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%");
            })->limit(10)->get();

            // Debug logging
            Log::info('Student search results', [
                'search' => $this->search,
                'count' => $students->count(),
                'students' => $students->toArray(),
            ]);

            return $students;
        }

        $this->showDropdown = false;

        return collect();
    }

    public function updatedSearch()
    {
        // Debug logging
        Log::info('Search updated', [
            'search' => $this->search,
            'length' => strlen($this->search),
        ]);

        // Clear any previous messages
        $this->message = '';  // Add this line to clear warning messages

        $this->selectedId = null;
        $this->selectedName = '';
        $this->showDropdown = strlen($this->search) >= 2;
    }

    public function selectStudent($id, $fname, $lname)
    {
        // Debug logging
        Log::info('Student selected', [
            'id' => $id,
            'name' => $fname.' '.$lname,
        ]);

        $this->selectedId = $id;
        $this->search = $fname.' '.$lname;
        $this->selectedName = $fname.' '.$lname;
        $this->showDropdown = false;
    }

    public function addParticipant()
    {
        $this->search = '';

        if (! $this->selectedId) {
            $this->message = 'Please select a student first.';

            return;
        }

        // Check if student is already registered
        $existingRegistration = $this->event->registrations()
            ->where('student_id', $this->selectedId)
            ->exists();

        if ($existingRegistration) {
            $this->search = '';
            $this->message = $this->selectedName.' is already registered for this event.';

            return;
        }

        // Create the registration
        $this->event->registrations()->create([
            'student_id' => $this->selectedId,
            'end_status' => 'registered',
        ]);

        // Reset the form
        $this->search = '';
        $this->selectedId = null;
        $this->selectedName = '';
        $this->dispatch('init-search');  // This will trigger the Alpine.js init

        // Dispatch flash message
        $this->dispatch(
            'flash-message',
            message: 'Student successfully added to the event.',
            type: 'success'
        );

        // Dispatch events
        $this->dispatch('registration-added');
        $this->dispatch('close-modal', 'add-participant');
    }

    public function clearSelection()
    {
        $this->selectedId = null;
        $this->selectedName = '';
        $this->search = '';
        $this->showDropdown = false;
        $this->message = ''; // Add this line to clear any previous messages
    }

    public function cancelAction()
    {
        $this->clearSelection(); // Clear any selected student or search input
        $this->dispatch('clear-messages'); // Dispatch a custom event to clear flash messages
    }

    public function render()
    {
        // Debug logging
        Log::info('Rendering search component', [
            'search' => $this->search,
            'showDropdown' => $this->showDropdown,
            'hasStudents' => isset($this->students) ? $this->students->count() : 0,
        ]);

        return view('livewire.student-search', [
            'students' => $this->students,
        ]);
    }
}
