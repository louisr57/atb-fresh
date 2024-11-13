<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FacilitatorController;
use App\Http\Controllers\VenueController;
use App\Models\Job;

// Public routes
Route::get('/contact', function () {
    return view('contact');
});

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

// Breeze Authentication Routes
require __DIR__ . '/auth.php';

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Home route (protected)
    Route::get('/', function () {
        return view('home', ['greeting' => 'It\'s another really beautiful day!']);
    });

    // Dashboard and Profile routes
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Course routes (all protected)
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{id}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{id}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');

    // Facilitator routes (all protected)
    Route::get('/facilitators', [FacilitatorController::class, 'index'])->name('facilitators.index');
    Route::get('/facilitators/create', [FacilitatorController::class, 'create'])->name('facilitators.create');
    Route::post('/facilitators', [FacilitatorController::class, 'store'])->name('facilitators.store');
    Route::get('/facilitators/{id}', [FacilitatorController::class, 'show'])->name('facilitators.show');
    Route::get('/facilitators/{facilitator}/edit', [FacilitatorController::class, 'edit'])->name('facilitators.edit');
    Route::put('/facilitators/{facilitator}', [FacilitatorController::class, 'update'])->name('facilitators.update');
    Route::delete('/facilitators/{id}', [FacilitatorController::class, 'destroy'])->name('facilitators.destroy');

    // Student routes (all protected)
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');

    // Event routes (all protected)
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');

    // Venue routes (all protected)
    Route::get('/venues', [VenueController::class, 'index'])->name('venues.index');
    Route::get('/venues/create', [VenueController::class, 'create'])->name('venues.create');
    Route::post('/venues', [VenueController::class, 'store'])->name('venues.store');
    Route::get('/venues/{id}', [VenueController::class, 'show'])->name('venues.show');
    Route::get('/venues/{venue}/edit', [VenueController::class, 'edit'])->name('venues.edit');
    Route::put('/venues/{venue}', [VenueController::class, 'update'])->name('venues.update');
    Route::delete('/venues/{id}', [VenueController::class, 'destroy'])->name('venues.destroy');

    // Registration routes (all protected)
    Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
    Route::get('/registrations/create/{event}', [RegistrationController::class, 'create'])->name('registrations.create');
    Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
});
