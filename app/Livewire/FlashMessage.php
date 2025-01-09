<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class FlashMessage extends Component
{
    public $message = '';

    public $type = '';

    public $show = false;

    #[On('flash-message')]
    public function showMessage($message, $type = 'success')
    {
        $this->message = $message;
        $this->type = $type;
        $this->show = true;
    }

    public function render()
    {
        return view('livewire.flash-message');
    }
}
