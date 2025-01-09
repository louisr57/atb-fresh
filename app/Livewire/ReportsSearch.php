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

        // Get filtered options for each dropdown
        $baseQuery = Registration::query()
            ->join('students', 'registrations.student_id', '=', 'students.id')
            ->join('events', 'registrations.event_id', '=', 'events.id')
            ->join('courses', 'events.course_id', '=', 'courses.id')
            ->join('venues', 'events.venue_id', '=', 'venues.id');

        // Apply current filters to each dropdown's options query
        $coursesQuery = clone $baseQuery;
        $statusesQuery = clone $baseQuery;
        $studentCitiesQuery = clone $baseQuery;
        $studentCountriesQuery = clone $baseQuery;
        $venueNamesQuery = clone $baseQuery;
        $venueCitiesQuery = clone $baseQuery;
        $venueStatesQuery = clone $baseQuery;
        $venueCountriesQuery = clone $baseQuery;

        // Apply filters for each query except its own field
        if ($this->search_course) {
            $statusesQuery->where('courses.course_title', 'like', '%' . $this->search_course . '%');
            $studentCitiesQuery->where('courses.course_title', 'like', '%' . $this->search_course . '%');
            $studentCountriesQuery->where('courses.course_title', 'like', '%' . $this->search_course . '%');
            $venueNamesQuery->where('courses.course_title', 'like', '%' . $this->search_course . '%');
            $venueCitiesQuery->where('courses.course_title', 'like', '%' . $this->search_course . '%');
            $venueStatesQuery->where('courses.course_title', 'like', '%' . $this->search_course . '%');
            $venueCountriesQuery->where('courses.course_title', 'like', '%' . $this->search_course . '%');
        }

        if ($this->search_status) {
            $coursesQuery->where('registrations.end_status', 'like', '%' . $this->search_status . '%');
            $studentCitiesQuery->where('registrations.end_status', 'like', '%' . $this->search_status . '%');
            $studentCountriesQuery->where('registrations.end_status', 'like', '%' . $this->search_status . '%');
            $venueNamesQuery->where('registrations.end_status', 'like', '%' . $this->search_status . '%');
            $venueCitiesQuery->where('registrations.end_status', 'like', '%' . $this->search_status . '%');
            $venueStatesQuery->where('registrations.end_status', 'like', '%' . $this->search_status . '%');
            $venueCountriesQuery->where('registrations.end_status', 'like', '%' . $this->search_status . '%');
        }

        if ($this->search_student_city) {
            $coursesQuery->where('students.city', 'like', '%' . $this->search_student_city . '%');
            $statusesQuery->where('students.city', 'like', '%' . $this->search_student_city . '%');
            $studentCountriesQuery->where('students.city', 'like', '%' . $this->search_student_city . '%');
            $venueNamesQuery->where('students.city', 'like', '%' . $this->search_student_city . '%');
            $venueCitiesQuery->where('students.city', 'like', '%' . $this->search_student_city . '%');
            $venueStatesQuery->where('students.city', 'like', '%' . $this->search_student_city . '%');
            $venueCountriesQuery->where('students.city', 'like', '%' . $this->search_student_city . '%');
        }

        if ($this->search_student_country) {
            $coursesQuery->where('students.country', 'like', '%' . $this->search_student_country . '%');
            $statusesQuery->where('students.country', 'like', '%' . $this->search_student_country . '%');
            $studentCitiesQuery->where('students.country', 'like', '%' . $this->search_student_country . '%');
            $venueNamesQuery->where('students.country', 'like', '%' . $this->search_student_country . '%');
            $venueCitiesQuery->where('students.country', 'like', '%' . $this->search_student_country . '%');
            $venueStatesQuery->where('students.country', 'like', '%' . $this->search_student_country . '%');
            $venueCountriesQuery->where('students.country', 'like', '%' . $this->search_student_country . '%');
        }

        if ($this->search_venue_name) {
            $coursesQuery->where('venues.venue_name', 'like', '%' . $this->search_venue_name . '%');
            $statusesQuery->where('venues.venue_name', 'like', '%' . $this->search_venue_name . '%');
            $studentCitiesQuery->where('venues.venue_name', 'like', '%' . $this->search_venue_name . '%');
            $studentCountriesQuery->where('venues.venue_name', 'like', '%' . $this->search_venue_name . '%');
            $venueCitiesQuery->where('venues.venue_name', 'like', '%' . $this->search_venue_name . '%');
            $venueStatesQuery->where('venues.venue_name', 'like', '%' . $this->search_venue_name . '%');
            $venueCountriesQuery->where('venues.venue_name', 'like', '%' . $this->search_venue_name . '%');
        }

        if ($this->search_venue_city) {
            $coursesQuery->where('venues.city', 'like', '%' . $this->search_venue_city . '%');
            $statusesQuery->where('venues.city', 'like', '%' . $this->search_venue_city . '%');
            $studentCitiesQuery->where('venues.city', 'like', '%' . $this->search_venue_city . '%');
            $studentCountriesQuery->where('venues.city', 'like', '%' . $this->search_venue_city . '%');
            $venueNamesQuery->where('venues.city', 'like', '%' . $this->search_venue_city . '%');
            $venueStatesQuery->where('venues.city', 'like', '%' . $this->search_venue_city . '%');
            $venueCountriesQuery->where('venues.city', 'like', '%' . $this->search_venue_city . '%');
        }

        if ($this->search_venue_state) {
            $coursesQuery->where('venues.state', 'like', '%' . $this->search_venue_state . '%');
            $statusesQuery->where('venues.state', 'like', '%' . $this->search_venue_state . '%');
            $studentCitiesQuery->where('venues.state', 'like', '%' . $this->search_venue_state . '%');
            $studentCountriesQuery->where('venues.state', 'like', '%' . $this->search_venue_state . '%');
            $venueNamesQuery->where('venues.state', 'like', '%' . $this->search_venue_state . '%');
            $venueCitiesQuery->where('venues.state', 'like', '%' . $this->search_venue_state . '%');
            $venueCountriesQuery->where('venues.state', 'like', '%' . $this->search_venue_state . '%');
        }

        if ($this->search_venue_country) {
            $coursesQuery->where('venues.country', 'like', '%' . $this->search_venue_country . '%');
            $statusesQuery->where('venues.country', 'like', '%' . $this->search_venue_country . '%');
            $studentCitiesQuery->where('venues.country', 'like', '%' . $this->search_venue_country . '%');
            $studentCountriesQuery->where('venues.country', 'like', '%' . $this->search_venue_country . '%');
            $venueNamesQuery->where('venues.country', 'like', '%' . $this->search_venue_country . '%');
            $venueCitiesQuery->where('venues.country', 'like', '%' . $this->search_venue_country . '%');
            $venueStatesQuery->where('venues.country', 'like', '%' . $this->search_venue_country . '%');
        }

        return view('livewire.reports-search', [
            'results' => $results,
            'courses' => $coursesQuery->select('courses.course_title')->distinct()->orderBy('courses.course_title')->pluck('course_title'),
            'statuses' => $statusesQuery->select('registrations.end_status')->distinct()->orderBy('registrations.end_status')->pluck('end_status'),
            'studentCities' => $studentCitiesQuery->select('students.city')->distinct()->orderBy('students.city')->pluck('city'),
            'studentCountries' => $studentCountriesQuery->select('students.country')->distinct()->orderBy('students.country')->pluck('country'),
            'venueNames' => $venueNamesQuery->select('venues.venue_name')->distinct()->orderBy('venues.venue_name')->pluck('venue_name'),
            'venueCities' => $venueCitiesQuery->select('venues.city')->distinct()->orderBy('venues.city')->pluck('city'),
            'venueStates' => $venueStatesQuery->select('venues.state')->distinct()->orderBy('venues.state')->pluck('state'),
            'venueCountries' => $venueCountriesQuery->select('venues.country')->distinct()->orderBy('venues.country')->pluck('country')
        ]);
    }
}
