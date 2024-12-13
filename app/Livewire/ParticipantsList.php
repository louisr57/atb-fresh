<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Event;
use App\Models\Registration;
use Livewire\Attributes\On;

class ParticipantsList extends Component
{
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
            $registration = Registration::findOrFail($registrationId);
            $studentName = $registration->student->first_name . ' ' . $registration->student->last_name;

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
        $registrations = $this->event->registrations()
            ->with(['student' => function ($query) {
                $query->orderBy($this->sortField, $this->sortDirection);
            }])
            ->when($this->sortField === 'end_status', function ($query) {
                $query->orderBy('end_status', $this->sortDirection);
            })
            ->get();

        // If sorting by name or email, we need to sort the collection after retrieval
        // since these fields are in the related student model
        if (in_array($this->sortField, ['first_name', 'email'])) {
            $registrations = $registrations->sortBy(function ($registration) {
                return $registration->student->{$this->sortField};
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }

        return view('livewire.participants-list', [
            'registrations' => $registrations
        ]);
    }
}
