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
    public $statuses = ['registered', 'withdrawn', 'complete', 'incomplete'];

    public function mount(Registration $registration)
    {
        $this->registration = $registration;
        $this->end_status = $registration->end_status;
        $this->comments = $registration->comments;
    }

    public function toggleEdit()
    {
        $this->isEditing = !$this->isEditing;
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
        return view('livewire.registration-edit');
    }
}
