<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

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
        'dob',
        'gender',
        'website'
    ];

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
}
