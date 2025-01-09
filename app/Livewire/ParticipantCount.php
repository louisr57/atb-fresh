<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Attributes\On;
use Livewire\Component;

class ParticipantCount extends Component
{
    public $event;

    public function mount(Event $event)
    {
        $this->event = $event;
    }

    #[On('registration-added')]
    #[On('registration-deleted')]
    public function refreshCount()
    {
        $this->event->refresh();
    }

    public function render()
    {
        return view('livewire.participant-count');
    }
}
