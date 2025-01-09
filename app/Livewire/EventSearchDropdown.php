<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Facilitator;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EventSearchDropdown extends Component
{
    public $search = '';

    public $suggestions = [];

    public $showDropdown = false;

    public $fieldType;

    public $selectedValue = '';

    public $name;

    public $placeholder;

    public function mount($fieldType, $name, $placeholder = '')
    {
        $this->fieldType = $fieldType;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->selectedValue = request($name, '');
        $this->search = $this->selectedValue;
    }

    public function updatedSearch()
    {
        if (strlen($this->search) < 1) {
            $this->suggestions = [];
            $this->showDropdown = false;

            return;
        }

        $searchTerm = '%'.$this->search.'%';

        $this->suggestions = match ($this->fieldType) {
            'course' => Course::where('course_title', 'like', $searchTerm)
                ->select('course_title as text')
                ->distinct()
                ->limit(10)
                ->pluck('text'),

            'facilitator' => Facilitator::where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', $searchTerm)
                ->select(DB::raw("CONCAT(first_name, ' ', last_name) as text"))
                ->distinct()
                ->limit(10)
                ->pluck('text'),

            'venue' => Venue::where('venue_name', 'like', $searchTerm)
                ->select('venue_name as text')
                ->distinct()
                ->limit(10)
                ->pluck('text'),

            'city' => Venue::where('city', 'like', $searchTerm)
                ->select('city as text')
                ->distinct()
                ->limit(10)
                ->pluck('text'),

            'state' => Venue::where('state', 'like', $searchTerm)
                ->select('state as text')
                ->distinct()
                ->limit(10)
                ->pluck('text'),

            'country' => Venue::where('country', 'like', $searchTerm)
                ->select('country as text')
                ->distinct()
                ->limit(10)
                ->pluck('text'),

            default => collect([]),
        };

        $this->showDropdown = count($this->suggestions) > 0;
    }

    public function selectSuggestion($value)
    {
        $this->search = $value;
        $this->selectedValue = $value;
        $this->showDropdown = false;
    }

    public function render()
    {
        return view('livewire.event-search-dropdown');
    }
}
