<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FacilitatorController;
use App\Models\Job;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return view('home', ['greeting' => 'It\'s another really beautiful day!']);
});

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
Route::get('/courses/{id}', [CourseController::class, 'show'])->name('courses.show');
Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');

Route::get('/facilitators', [FacilitatorController::class, 'index'])->name('facilitators.index');
Route::get('/facilitators/create', [FacilitatorController::class, 'create'])->name('facilitators.create');
Route::get('/facilitators/{facilitator}/edit', [FacilitatorController::class, 'edit'])->name('facilitators.edit');
Route::get('/facilitators/{id}', [FacilitatorController::class, 'show'])->name('facilitators.show');
Route::post('/facilitators', [FacilitatorController::class, 'store'])->name('facilitators.store');
Route::put('/facilitators/{facilitator}', [FacilitatorController::class, 'update'])->name('facilitators.update');
Route::delete('/facilitators/{id}', [FacilitatorController::class, 'destroy'])->name('facilitators.destroy');


Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');
Route::post('/students', [StudentController::class, 'store'])->name('students.store');

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');


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

// Route::middleware([])->group(function () {
//     Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
// });

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
