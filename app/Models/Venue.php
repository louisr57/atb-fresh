<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Venue extends Model
{
    use HasFactory, LogsActivity;

    const LOG_NAME = 'venue';

    protected $fillable = [
        'venue_name',
        'is_virtual',
        'address',
        'city',
        'state',
        'country',
        'postcode',
        'location_geocode',
        'vcontact_person',
        'vcontact_phone',
        'vcontact_email',
        'remarks',
    ];

    protected $casts = [
        'is_virtual' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'venue_name',
                'is_virtual',
                'address',
                'city',
                'state',
                'country',
                'postcode',
                'location_geocode',
                'vcontact_person',
                'vcontact_phone',
                'vcontact_email',
                'remarks',
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $venueName) => "Venue has been {$venueName}")
            ->useLogName(self::LOG_NAME);
    }

    /**
     * Venue name with an "(online)" suffix for virtual venues, for use in lists and pickers.
     */
    protected function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->venue_name.($this->is_virtual ? ' (online)' : ''));
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
