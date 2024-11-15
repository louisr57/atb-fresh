<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\Registration;
use Livewire\Component;

class StudentSearch extends Component
{
    public $eventId;
    public $search = '';
    public $selectedStudentId = null;
    public $selectedStudentName = '';

    public function mount($eventId)
    {
        $this->eventId = $eventId;
    }

    public function selectStudent($studentId)
    {
        $student = Student::find($studentId);
        $this->selectedStudentId = $studentId;
        $this->selectedStudentName = $student->first_name . ' ' . $student->last_name;
        $this->search = $this->selectedStudentName;
    }

    public function addParticipant()
    {
        $registration = Registration::create([
            'student_id' => $this->selectedStudentId,
            'event_id' => $this->eventId,
            'end_status' => 'registered'
        ]);

        session()->flash('success', 'Participant added successfully');
        return redirect()->route('events.show', $this->eventId);
    }

    public function render()
    {
        $students = [];
        if (strlen($this->search) >= 1 && !$this->selectedStudentId) {
            $students = Student::where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orderBy('first_name')
                ->limit(10)
                ->get();
        }

        return view('livewire.student-search', [
            'students' => $students
        ]);
    }
}
