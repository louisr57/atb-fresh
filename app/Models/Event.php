<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        // Remove the updated event observer since we're handling counts in batch
        // This prevents potential race conditions during seeding
    }

    // Define relationship with the Course model
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function facilitator()
    {
        return $this->belongsTo(Facilitator::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'event_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'registrations', 'event_id', 'student_id')->withTimestamps();
    }

    public function updateParticipantCount()
    {
        $this->participant_count = $this->registrations()->count();
        $this->saveQuietly(); // Use saveQuietly to prevent triggering observers
    }
}
