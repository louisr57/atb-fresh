<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Venue;

class VenueSearch extends Component
{
    public $search = '';
    public $venues = [];
    public $selectedVenueId = '';
    public $showDropdown = false;

    public function mount()
    {
        $this->venues = [];
    }

    public function updatedSearch()
    {
        $this->selectedVenueId = '';

        if (strlen($this->search) > 0) {
            $this->venues = Venue::where('venue_name', 'like', '%' . $this->search . '%')
                ->get();
            $this->showDropdown = true;
        } else {
            $this->venues = [];
            $this->showDropdown = false;
        }
    }

    public function selectVenue($venueId, $venueName)
    {
        $this->selectedVenueId = $venueId;
        $this->search = $venueName;
        $this->showDropdown = false;
        $this->venues = [];
    }

    public function render()
    {
        return view('livewire.venue-search');
    }
}
