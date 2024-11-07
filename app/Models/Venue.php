<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Define relationship with the Event model

    public function events()
    {
        return $this->hasMany(Event::class, 'event_id');
    }
}
