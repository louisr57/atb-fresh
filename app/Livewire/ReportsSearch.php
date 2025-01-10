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
    public $search_date_operator = '=';
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

    public function toggleSearch()
    {
        $this->showSearch = !$this->showSearch;
    }

    public $emailList = '';

    public $copiedEmails = false;

    public function getFormattedEmails()
    {
        $baseQuery = Registration::query()
            ->join('students', 'registrations.student_id', '=', 'students.id')
            ->join('events', 'registrations.event_id', '=', 'events.id')
            ->join('courses', 'events.course_id', '=', 'courses.id')
            ->join('venues', 'events.venue_id', '=', 'venues.id');

        // Apply all current filters
        if ($this->search_course) {
            $baseQuery->where('courses.course_title', '=', $this->search_course);
        }
        if ($this->search_status) {
            $baseQuery->where('registrations.end_status', '=', $this->search_status);
        }
        if ($this->search_date) {
            $baseQuery->where('events.datefrom', $this->search_date_operator, $this->search_date);
        }
        if ($this->search_student_city) {
            $baseQuery->where('students.city', '=', $this->search_student_city);
        }
        if ($this->search_student_country) {
            $baseQuery->where('students.country', '=', $this->search_student_country);
        }
        if ($this->search_venue_name) {
            $baseQuery->where('venues.venue_name', '=', $this->search_venue_name);
        }
        if ($this->search_venue_city) {
            $baseQuery->where('venues.city', '=', $this->search_venue_city);
        }
        if ($this->search_venue_state) {
            $baseQuery->where('venues.state', '=', $this->search_venue_state);
        }
        if ($this->search_venue_country) {
            $baseQuery->where('venues.country', '=', $this->search_venue_country);
        }

        $this->emailList = $baseQuery->select('students.first_name', 'students.last_name', 'students.email')
            ->get()
            ->map(function ($student) {
                return "{$student->first_name} {$student->last_name} <{$student->email}>";
            })
            ->join(', ');

        // After generating the list, copy to clipboard
        $this->js("
            navigator.clipboard.writeText('{$this->emailList}').then(() => {
                \$wire.set('copiedEmails', true);
                setTimeout(() => \$wire.set('copiedEmails', false), 5000);
            });
        ");
    }

    public function resetSearch()
    {
        $this->reset([
            'search_course',
            'search_status',
            'search_date_operator',
            'search_date',
            'search_student_city',
            'search_student_country',
            'search_venue_name',
            'search_venue_address',
            'search_venue_city',
            'search_venue_state',
            'search_venue_country',
            'emailList' // Clear the email list
        ]);
        $this->resetPage();
    }

    public function render()
    {
        // Build base query with all joins
        $baseQuery = Registration::query()
            ->join('students', 'registrations.student_id', '=', 'students.id')
            ->join('events', 'registrations.event_id', '=', 'events.id')
            ->join('courses', 'events.course_id', '=', 'courses.id')
            ->join('venues', 'events.venue_id', '=', 'venues.id');

        // Apply all current filters to create the filtered recordset
        if ($this->search_course) {
            $baseQuery->where('courses.course_title', '=', $this->search_course);
        }
        if ($this->search_status) {
            $baseQuery->where('registrations.end_status', '=', $this->search_status);
        }
        if ($this->search_date) {
            $baseQuery->where('events.datefrom', $this->search_date_operator, $this->search_date);
        }
        if ($this->search_student_city) {
            $baseQuery->where('students.city', '=', $this->search_student_city);
        }
        if ($this->search_student_country) {
            $baseQuery->where('students.country', '=', $this->search_student_country);
        }
        if ($this->search_venue_name) {
            $baseQuery->where('venues.venue_name', '=', $this->search_venue_name);
        }
        if ($this->search_venue_address) {
            $baseQuery->where('venues.address', '=', $this->search_venue_address);
        }
        if ($this->search_venue_city) {
            $baseQuery->where('venues.city', '=', $this->search_venue_city);
        }
        if ($this->search_venue_state) {
            $baseQuery->where('venues.state', '=', $this->search_venue_state);
        }
        if ($this->search_venue_country) {
            $baseQuery->where('venues.country', '=', $this->search_venue_country);
        }

        // Clone the filtered base query for results
        $resultsQuery = clone $baseQuery;
        $results = $resultsQuery->select(
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
        )->paginate(10);

        // Get dropdown options from the filtered recordset
        $coursesQuery = clone $baseQuery;
        $statusesQuery = clone $baseQuery;
        $studentCitiesQuery = clone $baseQuery;
        $studentCountriesQuery = clone $baseQuery;
        $venueNamesQuery = clone $baseQuery;
        $venueCitiesQuery = clone $baseQuery;
        $venueStatesQuery = clone $baseQuery;
        $venueCountriesQuery = clone $baseQuery;

        // Remove each field's own filter when getting its options
        if ($this->search_course) $coursesQuery->where('courses.course_title', '=', $this->search_course);
        if ($this->search_status) $statusesQuery->where('registrations.end_status', '=', $this->search_status);
        if ($this->search_student_city) $studentCitiesQuery->where('students.city', '=', $this->search_student_city);
        if ($this->search_student_country) $studentCountriesQuery->where('students.country', '=', $this->search_student_country);
        if ($this->search_venue_name) $venueNamesQuery->where('venues.venue_name', '=', $this->search_venue_name);
        if ($this->search_venue_city) $venueCitiesQuery->where('venues.city', '=', $this->search_venue_city);
        if ($this->search_venue_state) $venueStatesQuery->where('venues.state', '=', $this->search_venue_state);
        if ($this->search_venue_country) $venueCountriesQuery->where('venues.country', '=', $this->search_venue_country);

        return view('livewire.reports-search', [
            'results' => $results,
            'dateOperators' => ['=', '>=', '>', '<=', '<'],
            'courses' => $coursesQuery->select('courses.course_title')->distinct()->orderBy('courses.course_title')->pluck('course_title'),
            'statuses' => $statusesQuery->select('registrations.end_status')->distinct()->orderBy('registrations.end_status')->pluck('end_status'),
            'studentCities' => $studentCitiesQuery->select('students.city')->whereNotNull('students.city')->distinct()->orderBy('students.city')->pluck('city'),
            'studentCountries' => $studentCountriesQuery->select('students.country')->whereNotNull('students.country')->distinct()->orderBy('students.country')->pluck('country'),
            'venueNames' => $venueNamesQuery->select('venues.venue_name')->whereNotNull('venues.venue_name')->distinct()->orderBy('venues.venue_name')->pluck('venue_name'),
            'venueCities' => $venueCitiesQuery->select('venues.city')->whereNotNull('venues.city')->distinct()->orderBy('venues.city')->pluck('city'),
            'venueStates' => $venueStatesQuery->select('venues.state')->whereNotNull('venues.state')->distinct()->orderBy('venues.state')->pluck('state'),
            'venueCountries' => $venueCountriesQuery->select('venues.country')->whereNotNull('venues.country')->distinct()->orderBy('venues.country')->pluck('country')
        ]);
    }
}
