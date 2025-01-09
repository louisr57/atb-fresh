<?php

use App\Models\Event;
use App\Models\Student;

// Example 1: Query all students
$students = Student::all();

// Example 2: Get count of all events
$eventCount = Event::count();

// Example 3: Complex query with relationship
$activeEvents = Event::with('facilitators')
    ->whereHas('registrations', function ($query) {
        $query->where('status', 'confirmed');
    })
    ->get();

// Example 4: Create a new student
$newStudent = new Student;
$newStudent->name = 'Test Student';
$newStudent->email = 'test@example.com';
// Don't save in example, just demonstrate object creation
