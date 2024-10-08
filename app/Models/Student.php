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
        return $this->hasMany(Registration::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'registrations', 'event_id', 'student_id')->withTimestamps(); // The system suggested adding ", 'registrations'" as a second argument.
    }

    public function facilitators()
    {
        return $this->belongsToMany(Event::class, 'registrations', 'student_id', 'event_id')
            ->join('facilitators as event_facilitators', 'events.facilitator_id', '=', 'event_facilitators.id')
            ->select('event_facilitators.*')
            ->distinct()
            ->withTimestamps();
    }
}
