<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;
use Livewire\WithPagination;

class StudentsIndexSearch extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'first_name';
    public $direction = 'asc';

    public function mount()
    {
        $this->sortBy = request('sort_by', 'first_name');
        $this->direction = request('direction', 'asc');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sort($field)
    {
        if ($field === $this->sortBy) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->direction = 'asc';
        }
    }

    public function getStudentsProperty()
    {
        return Student::where(function ($query) {
            if (strlen($this->search) >= 2) {
                $query->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%");
            }
        })
        ->orderBy($this->sortBy, $this->direction)
        ->paginate(10);
    }

    public function render()
    {
        return view('livewire.students-index-search', [
            'students' => $this->students,
        ]);
    }
}
