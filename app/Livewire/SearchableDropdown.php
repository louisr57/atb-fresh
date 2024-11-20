<?php

namespace App\Livewire;

use Livewire\Component;

class SearchableDropdown extends Component
{
    public $search = '';
    public $items = ['Apple', 'Banana', 'Cherry', 'Date', 'Fig', 'Grape'];
    public $selectedItem = null;

    public function render()
    {
        return view('livewire.searchable-dropdown');
    }

    public function updatedSearch()
    {
        // This will automatically re-render the component when search changes
    }
}
