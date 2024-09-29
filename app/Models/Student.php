<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'student_id');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class); // The system suggested adding ", 'registrations'" as a second argument.
    }
}
