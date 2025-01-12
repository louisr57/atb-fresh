<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Course extends Model
{
    use HasFactory, LogsActivity;

    const LOG_NAME = 'course';

    protected $fillable = [
        'course_code',
        'course_title',
        'description',
        'prerequisites',
        'duration'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'course_code',
                'course_title',
                'description',
                'prerequisites',
                'duration'
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Course has been {$eventName}")
            ->useLogName(self::LOG_NAME);
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'course_id');
    }
}
