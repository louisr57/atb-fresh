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

    public function mount(Event $event)
    {
        $this->event = $event;
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
        return view('livewire.participants-list', [
            'registrations' => $this->event->registrations()->with('student')->get()
        ]);
    }
}
