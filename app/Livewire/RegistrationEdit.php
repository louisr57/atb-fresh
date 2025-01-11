<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Registration;

class RegistrationEdit extends Component
{
    public Registration $registration;
    public $isEditing = false;
    public $end_status;
    public $comments;
    public $statuses = ['Registered', 'Withdrawn', 'Completed', 'Incomplete'];

    public function mount(Registration $registration)
    {
        $this->registration = $registration;
        $this->end_status = $registration->end_status;
        $this->comments = $registration->comments;
    }

    public function toggleEdit()
    {
        $this->isEditing = !$this->isEditing;
        if ($this->isEditing) {
            // Refresh values when entering edit mode
            $this->end_status = $this->registration->end_status;
            $this->comments = $this->registration->comments;
        }
    }

    public function save()
    {
        $this->registration->update([
            'end_status' => $this->end_status,
            'comments' => $this->comments
        ]);

        $this->isEditing = false;
        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.registration-edit');
    }
}
