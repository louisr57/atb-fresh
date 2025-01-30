<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Student extends Model
{
    use HasFactory, LogsActivity;

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
}
