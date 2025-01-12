<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'event_id',
        'end_status',
        'comments'
    ];

    // Define relationship with the Student model
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Define relationship with the Event model
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function nextInEvent()
    {
        return $this->join('students', 'registrations.student_id', '=', 'students.id')
            ->where('registrations.event_id', $this->event_id)
            ->where(function($query) {
                $query->whereRaw("CONCAT(students.first_name, ' ', students.last_name) > ?",
                    [$this->student->first_name . ' ' . $this->student->last_name])
                ->orWhere(function($q) {
                    $q->whereRaw("CONCAT(students.first_name, ' ', students.last_name) = ?",
                        [$this->student->first_name . ' ' . $this->student->last_name])
                    ->where('registrations.id', '>', $this->id);
                });
            })
            ->orderByRaw("CONCAT(students.first_name, ' ', students.last_name) ASC")
            ->orderBy('registrations.id', 'asc')
            ->select('registrations.*')
            ->first();
    }

    public function previousInEvent()
    {
        return $this->join('students', 'registrations.student_id', '=', 'students.id')
            ->where('registrations.event_id', $this->event_id)
            ->where(function($query) {
                $query->whereRaw("CONCAT(students.first_name, ' ', students.last_name) < ?",
                    [$this->student->first_name . ' ' . $this->student->last_name])
                ->orWhere(function($q) {
                    $q->whereRaw("CONCAT(students.first_name, ' ', students.last_name) = ?",
                        [$this->student->first_name . ' ' . $this->student->last_name])
                    ->where('registrations.id', '<', $this->id);
                });
            })
            ->orderByRaw("CONCAT(students.first_name, ' ', students.last_name) DESC")
            ->orderBy('registrations.id', 'desc')
            ->select('registrations.*')
            ->first();
    }

    public function getPositionInEvent()
    {
        // Get all registrations for this event ordered by student name
        $orderedRegistrations = $this->join('students', 'registrations.student_id', '=', 'students.id')
            ->where('registrations.event_id', $this->event_id)
            ->orderByRaw("CONCAT(students.first_name, ' ', students.last_name) ASC")
            ->orderBy('registrations.id', 'asc')
            ->select('registrations.*')
            ->get();

        // Find position of current registration (1-based index)
        $position = $orderedRegistrations->search(function($item) {
            return $item->id === $this->id;
        }) + 1;

        return [
            'current' => $position,
            'total' => $orderedRegistrations->count()
        ];
    }
}
