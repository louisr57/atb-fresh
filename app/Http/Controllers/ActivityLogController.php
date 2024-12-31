<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\Student;

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
        $query = Activity::with(['causer', 'subject'])
            ->where('log_name', 'student')
            ->latest();

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
            $query->whereHas('causer', function($q) use ($request) {
                $q->where('name', $request->user);
            });
        }

        $activities = $query->paginate(20);

        return view('activity-logs.index', compact('activities'));
    }

    public function studentLogs($studentId)
    {
        $student = Student::findOrFail($studentId);

        $activities = Activity::with(['causer'])
            ->where('subject_type', Student::class)
            ->where('subject_id', $studentId)
            ->latest()
            ->paginate(20);

        return view('activity-logs.student', compact('activities', 'student'));
    }
}
