<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'event_id',
        'end_status',
        'comments'
    ];

    // Define relationship with the Student model
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Define relationship with the Event model
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
