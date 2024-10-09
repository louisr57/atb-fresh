<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Define relationship with the Course model
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function facilitator()
    {
        return $this->belongsTo(Facilitator::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'event_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'registrations', 'event_id', 'student_id')->withTimestamps();
    }
}
