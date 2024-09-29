<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'student_id');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'registrations', 'event_id', 'student_id')->withTimestamps(); // The system suggested adding ", 'registrations'" as a second argument.
    }

    public function instructors()
    {
        return $this->belongsToMany(Event::class, 'registrations', 'student_id', 'event_id')
            ->join('instructors as event_instructors', 'events.instructor_id', '=', 'event_instructors.id')
            ->select('event_instructors.*')
            ->distinct()
            ->withTimestamps();
    }
}
