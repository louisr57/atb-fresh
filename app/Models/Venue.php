<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Venue extends Model
{
    use HasFactory, LogsActivity;

    const LOG_NAME = 'venue';

    protected $fillable = [
        'venue_name',
        'address',
        'city',
        'state',
        'country',
        'postcode',
        'location_geocode',
        'remarks',
    ];

    // Define relationship with the Event model

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'venue_name',
                'address',
                'city',
                'state',
                'country',
                'postcode',
                'location_geocode',
                'remarks',
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $venueName) => "Venue has been {$venueName}")
            ->useLogName(self::LOG_NAME);
    }
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
