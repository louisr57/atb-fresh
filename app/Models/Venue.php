<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_name',
        'address',
        'city',
        'state',
        'country',
        'postcode',
        'location_geocode',
        'remarks'
    ];

    // Define relationship with the Event model
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
