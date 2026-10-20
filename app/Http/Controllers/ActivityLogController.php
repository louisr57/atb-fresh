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
            $modelMap = [
                'student' => Student::class,
                'course' => Course::class,
                'event' => Event::class,
                'facilitator' => Facilitator::class,
                'registration' => Registration::class,
                'venue' => Venue::class,
            ];

            if (isset($modelMap[$request->model])) {
                $query->where('subject_type', $modelMap[$request->model]);

                // If subject_id is provided, filter by that specific model instance
                if ($request->filled('subject_id')) {
                    $query->where('subject_id', $request->subject_id);
                }
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
            if ($request->user === 'System') {
                $query->whereNull('causer_id');
            } else {
                $query->whereHas('causer', function ($q) use ($request) {
                    $q->where('name', $request->user);
                });
            }
        }

        $activities = $query->paginate(20);

        return view('activity-logs.index', compact('activities'));
    }

    public function studentLogs($studentId)
    {
        // Include soft-deleted students so their history stays viewable
        $student = Student::withTrashed()->findOrFail($studentId);

        $activities = Activity::with(['causer'])
            ->where('subject_type', Student::class)
            ->where('subject_id', $studentId)
            ->latest()
            ->paginate(20);

        return view('activity-logs.student', compact('activities', 'student'));
    }
}
