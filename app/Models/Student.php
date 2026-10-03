<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Student extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    const LOG_NAME = 'student';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'city',
        'state',
        'country',
        'post_code',
        'occupation',
        'dob',
        'gender',
        'website',
        'ident',
        'next_of_kin',
        'allergies',
        'special_needs',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'address',
                'city',
                'state',
                'country',
                'post_code',
                'occupation',
                'dob',
                'gender',
                'website',
                'ident',
                'next_of_kin',
                'allergies',
                'special_needs',
                'reg_count',
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $studentName) => "Student has been {$studentName}")
            ->useLogName(self::LOG_NAME);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    protected static function booted()
    {
        // The DB-level cascadeOnDelete foreign key only fires on a hard DELETE,
        // so when a Student is soft-deleted we need to soft-delete their
        // registrations ourselves to keep behavior consistent with the old
        // cascade-delete expectations (and to make restore() meaningful).
        static::deleting(function (Student $student) {
            if (! $student->isForceDeleting()) {
                $student->registrations()->get()->each->delete();
            }
        });

        static::restoring(function (Student $student) {
            $student->registrations()->onlyTrashed()->get()->each->restore();
        });
    }
}
