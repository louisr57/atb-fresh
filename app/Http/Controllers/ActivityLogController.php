<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Event;
use App\Models\Facilitator;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Venue;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

// Following TODO extension terms are just for reminding me to use them in the future
// I've seen many places in the codebase where other authors have used these.
// FIXME:  ... more details here ... I can also use NOTE, REVIEW, DEBUG, HACK, IDEA, or any other comment tags
// TODO: ... more details here ... see TODO extension for more details
// QUESTION: ...
// IDEA:  ...
// REVIEW: ...

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])->latest();

        // Filter by model type and subject_id if provided
        if ($request->filled('model')) {
            $query->where('log_name', $request->model);

            // If subject_id is provided, filter by that specific model instance
            if ($request->filled('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }
        }

        // Filter by action type if provided
        if ($request->filled('action')) {
            $query->where('description', 'like', "%{$request->action}%");
        }

        // Filter by date range only if dates are provided and valid
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by user if provided
        if ($request->filled('user')) {
            $query->whereHas('causer', function ($q) use ($request) {
                $q->where('name', $request->user);
            });
        }

        $activities = $query->paginate(20);

        return view('activity-logs.index', compact('activities'));
    }

    public function studentLogs($studentId)
    {
        $student = (new Student)->findOrFail($studentId);

        $activities = Activity::with(['causer'])
            ->where('subject_type', Student::class)
            ->where('subject_id', $studentId)
            ->latest()
            ->paginate(20);

        return view('activity-logs.student', compact('activities', 'student'));
    }

    public function courseLogs($courseId)
    {
        $course = (new Course)->findOrFail($courseId);

        $activities = Activity::with(['causer'])
            ->where('subject_type', Course::class)
            ->where('subject_id', $courseId)
            ->latest()
            ->paginate(20);

        return view('activity-logs.course', compact('activities', 'course'));
    }

    public function facilitatorLogs($facilitatorId)
    {
        $facilitator = (new Facilitator)->findOrFail($facilitatorId);

        $activities = Activity::with(['causer'])
            ->where('subject_type', Facilitator::class)
            ->where('subject_id', $facilitatorId)
            ->latest()
            ->paginate(20);

        return view('activity-logs.facilitator', compact('activities', 'facilitator'));
    }

    public function eventLogs($eventId)
    {
        $event = (new Event)->findOrFail($eventId);

        $activities = Activity::with(['causer'])
            ->where('subject_type', Event::class)
            ->where('subject_id', $eventId)
            ->latest()
            ->paginate(20);

        return view('activity-logs.event', compact('activities', 'event'));
    }

    public function registrationLogs($registrationId)
    {
        $registration = (new Registration)->findOrFail($registrationId);

        $activities = Activity::with(['causer'])
            ->where('subject_type', Registration::class)
            ->where('subject_id', $registrationId)
            ->latest()
            ->paginate(20);

        return view('activity-logs.registration', compact('activities', 'registration'));
    }

    public function venueLogs($venueId)
    {
        $venue = (new Venue)->findOrFail($venueId);

        $activities = Activity::with(['causer'])
            ->where('subject_type', Venue::class)
            ->where('subject_id', $venueId)
            ->latest()
            ->paginate(20);

        return view('activity-logs.venue', compact('activities', 'venue'));
    }
}
