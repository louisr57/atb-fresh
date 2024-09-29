<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InstructorController;
use App\Models\Job;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return view('home', ['greeting' => 'It\'s another really beautiful day!']);
});

Route::get('/test-student/{id}', function ($id) {
    $controller = new \App\Http\Controllers\StudentController();
    $student = \App\Models\Student::find($id);
    return $controller->show($student);
});


Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{id}', [CourseController::class, 'show'])->name('courses.show');

Route::get('/instructors', [InstructorController::class, 'index'])->name('instructors.index');
Route::get('/instructors/{id}', [InstructorController::class, 'show'])->name('instructors.show');

Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');

Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');

Route::get('/practice', [PracticeController::class, 'index'])->name('practice');


Route::get('/jobs', function () {
    return view('jobs', [
        'jobs' => Job::all()
    ]);
});

Route::get('/jobs/{id}', function ($id) {

    $job = Job::find($id);

    return view('job', [
        'job' => $job
    ]);
});

Route::get('/contact', function () {
    return view('contact');
});

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
