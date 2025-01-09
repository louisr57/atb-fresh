<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Registration;
use App\Models\Event;
use App\Models\Student;
use App\Models\Venue;
use Livewire\Component;
use Livewire\WithPagination;

class ReportsSearch extends Component
{
    use WithPagination;

    public $showSearch = true;
    public $search_course;
    public $search_status;
    public $search_date;
    public $search_student_city;
    public $search_student_country;
    public $search_venue_name;
    public $search_venue_address;
    public $search_venue_city;
    public $search_venue_state;
    public $search_venue_country;

    protected $queryString = [
        'search_course' => ['except' => ''],
        'search_status' => ['except' => ''],
        'search_date' => ['except' => ''],
        'search_student_city' => ['except' => ''],
        'search_student_country' => ['except' => ''],
        'search_venue_name' => ['except' => ''],
        'search_venue_address' => ['except' => ''],
        'search_venue_city' => ['except' => ''],
        'search_venue_state' => ['except' => ''],
        'search_venue_country' => ['except' => '']
    ];

    public function resetSearch()
    {
        $this->reset([
            'search_course',
            'search_status',
            'search_date',
            'search_student_city',
            'search_student_country',
            'search_venue_name',
            'search_venue_address',
            'search_venue_city',
            'search_venue_state',
            'search_venue_country'
        ]);
        $this->resetPage();
    }

    public function toggleSearch()
    {
        $this->showSearch = !$this->showSearch;
    }

    public function render()
    {
        $query = Registration::query()
            ->join('students', 'registrations.student_id', '=', 'students.id')
            ->join('events', 'registrations.event_id', '=', 'events.id')
            ->join('courses', 'events.course_id', '=', 'courses.id')
            ->join('venues', 'events.venue_id', '=', 'venues.id')
            ->select(
                'students.first_name',
                'students.last_name',
                'students.email',
                'courses.course_title',
                'registrations.end_status',
                'events.datefrom',
                'students.city as student_city',
                'students.country as student_country',
                'venues.venue_name',
                'venues.address',
                'venues.city as venue_city',
                'venues.state as venue_state',
                'venues.country as venue_country'
            );

        if ($this->search_course) {
            $query->where('courses.course_title', 'like', '%' . $this->search_course . '%');
        }

        if ($this->search_status) {
            $query->where('registrations.end_status', 'like', '%' . $this->search_status . '%');
        }

        if ($this->search_date) {
            $query->where('events.datefrom', '=', $this->search_date);
        }

        if ($this->search_student_city) {
            $query->where('students.city', 'like', '%' . $this->search_student_city . '%');
        }

        if ($this->search_student_country) {
            $query->where('students.country', 'like', '%' . $this->search_student_country . '%');
        }

        if ($this->search_venue_name) {
            $query->where('venues.venue_name', 'like', '%' . $this->search_venue_name . '%');
        }

        if ($this->search_venue_address) {
            $query->where('venues.address', 'like', '%' . $this->search_venue_address . '%');
        }

        if ($this->search_venue_city) {
            $query->where('venues.city', 'like', '%' . $this->search_venue_city . '%');
        }

        if ($this->search_venue_state) {
            $query->where('venues.state', 'like', '%' . $this->search_venue_state . '%');
        }

        if ($this->search_venue_country) {
            $query->where('venues.country', 'like', '%' . $this->search_venue_country . '%');
        }

        $results = $query->paginate(10);

        return view('livewire.reports-search', [
            'results' => $results,
            'courses' => Course::query()->pluck('course_title'),
            'statuses' => Registration::query()->select('end_status')->distinct()->pluck('end_status'),
            'studentCities' => Student::query()->select('city')->distinct()->pluck('city'),
            'studentCountries' => Student::query()->select('country')->distinct()->pluck('country'),
            'venueNames' => Venue::query()->select('venue_name')->distinct()->pluck('venue_name'),
            'venueCities' => Venue::query()->select('city')->distinct()->pluck('city'),
            'venueStates' => Venue::query()->select('state')->distinct()->pluck('state'),
            'venueCountries' => Venue::query()->select('country')->distinct()->pluck('country')
        ]);
    }
}
