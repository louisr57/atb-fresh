<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class UserSearchDropdown extends Component
{
    public $search = '';

    public $users = [];

    public $selectedUser = '';

    public $showDropdown = false;

    public function mount()
    {
        // Initialize with the value from the request if it exists
        if (request()->filled('user')) {
            $this->selectedUser = request()->user;
            $this->search = request()->user;
        }
    }

    public function updatedSearch()
    {
        if (strlen($this->search) > 0) {
            $this->users = User::where('name', 'like', '%'.$this->search.'%')
                ->limit(5)
                ->get();
            $this->showDropdown = true;
        } else {
            $this->users = [];
            $this->showDropdown = false;
        }
    }

    public function selectUser($userId, $userName)
    {
        $this->selectedUser = $userName;
        $this->search = $userName;
        $this->showDropdown = false;
        $this->dispatch('userSelected', ['user' => $userName]);
    }

    public function clearSelection()
    {
        $this->selectedUser = '';
        $this->search = '';
        $this->showDropdown = false;
        $this->dispatch('userSelected', ['user' => '']);
    }

    public function render()
    {
        return view('livewire.user-search-dropdown');
    }
}
