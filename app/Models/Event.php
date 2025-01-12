<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Event extends Model
{
    use HasFactory, LogsActivity;

    const LOG_NAME = 'event';

    protected $fillable = [
        'title',
        'datefrom',
        'dateto',
        'timefrom',
        'timeto',
        'venue_id',
        'course_id',
        'facilitator_id',
        'remarks',
        'participant_count'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'datefrom',
                'dateto',
                'timefrom',
                'timeto',
                'venue_id',
                'course_id',
                'facilitator_id',
                'remarks',
                'participant_count'
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Event has been {$eventName}")
            ->useLogName(self::LOG_NAME);
    }

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

    public function facilitators()
    {
        return $this->belongsToMany(Facilitator::class)
            ->withTimestamps();
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
