<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Registration;
use Livewire\Attributes\On;

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

    #[On('startEdit')]
    public function startEdit()
    {
        $this->isEditing = true;
        $this->end_status = $this->registration->end_status;
        $this->comments = $this->registration->comments;
    }

    #[On('cancelEdit')]
    public function cancelEdit()
    {
        $this->isEditing = false;
    }

    #[On('saveChanges')]
    public function saveChanges()
    {
        $validated = $this->validate([
            'end_status' => 'required|in:' . implode(',', $this->statuses),
            'comments' => 'nullable|string'
        ]);


        $this->registration->update($validated);
        $this->registration->refresh();

        $this->end_status = $this->registration->end_status;
        $this->comments = $this->registration->comments;

        $this->isEditing = false;

        $this->dispatch('registration-updated');
    }

    public function render()
    {
        return view('livewire.registration-edit');
    }
}
