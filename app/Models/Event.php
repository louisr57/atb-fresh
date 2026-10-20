<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Event extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

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
        'participant_count',
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
                'participant_count',
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Event has been {$eventName}")
            ->useLogName(self::LOG_NAME);
    }

    protected static function booted()
    {
        // Remove the updated event observer since we're handling counts in batch
        // This prevents potential race conditions during seeding

        // The DB-level cascadeOnDelete foreign key only fires on a hard DELETE,
        // so when an Event is soft-deleted we need to soft-delete its
        // registrations ourselves to keep behavior consistent with the old
        // cascade-delete expectations (and to make restore() meaningful).
        static::deleting(function (Event $event) {
            if (! $event->isForceDeleting()) {
                $event->registrations()->get()->each->delete();
            }
        });

        static::restoring(function (Event $event) {
            $event->registrations()->onlyTrashed()->get()->each->restore();
        });
    }

    // Define relationship with the Course model
    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return BelongsToMany<Facilitator, $this> */
    public function facilitators(): BelongsToMany
    {
        return $this->belongsToMany(Facilitator::class)
            ->withTimestamps();
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** @return HasMany<Registration, $this> */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'event_id');
    }

    /** @return BelongsToMany<Student, $this> */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'registrations', 'event_id', 'student_id')->withTimestamps();
    }

    public function updateParticipantCount()
    {
        $this->participant_count = $this->registrations()->count();
        $this->saveQuietly(); // Use saveQuietly to prevent triggering observers
    }

    public function isEmpty()
    {
        return $this->registrations()->count() === 0;
    }
}
