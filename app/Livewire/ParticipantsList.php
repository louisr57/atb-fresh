<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Registration;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ParticipantsList extends Component
{
    use WithPagination;

    public $event;

    public $isDeleting = false;

    public $sortField = 'first_name';

    public $sortDirection = 'asc';

    public function mount(Event $event)
    {
        $this->event = $event;
    }

    public function sortBy($field)
    {
        if ($field === 'name') {
            $field = 'first_name'; // Sort by first_name when clicking name header
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[On('registration-added')]
    public function refreshParticipants()
    {
        $this->event->refresh();
    }

    public function deleteRegistration($registrationId)
    {
        if ($this->isDeleting) {
            return;
        }

        $this->isDeleting = true;

        try {
            $registration = Registration::query()->findOrFail($registrationId);
            $studentName = $registration->student->first_name.' '.$registration->student->last_name;

            // Add a small delay to ensure proper handling
            usleep(500000); // 0.5 second delay

            $registration->delete();

            $this->dispatch(
                'flash-message',
                message: "Successfully removed {$studentName} from the event.",
                type: 'success'
            );

            $this->dispatch('registration-deleted');
            $this->event->refresh();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->dispatch(
                'flash-message',
                message: 'Registration not found. It may have been already deleted.',
                type: 'error'
            );
        } catch (\Exception $e) {
            $this->dispatch(
                'flash-message',
                message: 'Error removing participant from the event. Please try again.',
                type: 'error'
            );
        } finally {
            $this->isDeleting = false;
        }
    }

    public function render()
    {
        $query = $this->event->registrations()->with('student');

        // Handle sorting
        if ($this->sortField === 'end_status') {
            // Sort by end_status directly on registrations table
            $query->orderBy('end_status', $this->sortDirection);
        } elseif (in_array($this->sortField, ['first_name', 'email'])) {
            // Sort by student fields using a join
            $query->join('students', 'registrations.student_id', '=', 'students.id')
                ->orderBy('students.'.$this->sortField, $this->sortDirection)
                ->select('registrations.*'); // Ensure we only get registration fields
        }

        $registrations = $query->paginate(20);

        return view('livewire.participants-list', [
            'registrations' => $registrations,
        ]);
    }
}
