<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Registration;

class RegistrationDetails extends Component
{
    public Registration $registration;
    public $isEditing = false;
    public $end_status;
    public $comments;

    public function mount(Registration $registration)
    {
        $this->registration = $registration;
        $this->end_status = $registration->end_status;
        $this->comments = $registration->comments;
    }

    public function startEditing()
    {
        $this->isEditing = true;
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->end_status = $this->registration->end_status;
        $this->comments = $this->registration->comments;
    }

    public function save()
    {
        $this->registration->update([
            'end_status' => $this->end_status,
            'comments' => $this->comments
        ]);

        $this->isEditing = false;
    }

    public function render()
    {
        return view('livewire.registration-details', [
            'statuses' => ['Registered', 'Withdrawn', 'Completed', 'Incomplete']
        ]);
    }
}
