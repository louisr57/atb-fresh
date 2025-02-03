<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Facilitator extends Model
{
    use HasFactory, LogsActivity;

    const LOG_NAME = 'facilitator';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'gender',
        'email',
        'phone_number',
        'address',
        'city',
        'state',
        'country',
        'post_code',
        'website',
        'dob'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'user_id',
                'first_name',
                'last_name',
                'gender',
                'email',
                'phone_number',
                'address',
                'city',
                'state',
                'country',
                'post_code',
                'website',
                'dob'
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $facilitatorName) => "Facilitator has been {$facilitatorName}")
            ->useLogName(self::LOG_NAME);
    }

    protected $casts = [
        'dob' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class)
            ->withTimestamps();
    }
}
