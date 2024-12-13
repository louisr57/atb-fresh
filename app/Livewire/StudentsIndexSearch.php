<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;
use Livewire\WithPagination;

class StudentsIndexSearch extends Component
{
    use WithPagination;

    public $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function getStudentsProperty()
    {
        return Student::where(function ($query) {
            if (strlen($this->search) >= 2) {
                $query->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%");
            }
        })
        ->orderBy(request('sort_by', 'first_name'), request('direction', 'asc'))
        ->paginate(10);
    }

    public function render()
    {
        return view('livewire.students-index-search', [
            'students' => $this->students,
        ]);
    }
}
