<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\AssignmentExtension;
use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Admin Roles & Permissions doc §2.3: Tutor can "Grant assignment due-date
 * extensions with reason." v1 scope: records and lists extensions for the
 * Tutor's own classes. It does not (yet) surface the extended date inside
 * the student-facing FullCalendar widget - that's a separate, more involved
 * change to shared calendar JS, called out as a deliberate fast-follow.
 */
class AssignmentExtensionController extends Controller
{
    public function index(Request $request)
    {
        $tutor = $request->user();
        abort_unless($tutor->isTutor(), 403);

        $classIds = $tutor->tutoredClasses()->pluck('classes.id');

        $events = CalendarEvent::whereIn('classId', $classIds)
            ->where('end', '>=', now()->subMonths(1))
            ->orderBy('end')
            ->get();

        $students = User::where('role', 4)->whereIn('grade', $classIds)
            ->orderBy('name')->get(['id', 'name', 'nickname', 'grade']);

        $extensions = AssignmentExtension::whereIn('calendar_event_id', $events->pluck('id'))
            ->with(['calendarEvent:id,title,end', 'student:id,name,nickname'])
            ->latest()
            ->get();

        return view('tutor.extensions.index', compact('events', 'students', 'extensions'));
    }

    public function store(Request $request)
    {
        $tutor = $request->user();
        abort_unless($tutor->isTutor(), 403);

        $data = $request->validate([
            'calendar_event_id' => ['required', 'integer', 'exists:calendar_events,id'],
            'student_id' => ['required', 'integer'],
            'extended_to' => ['required', 'date'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        abort_unless($tutor->canManageStudent($data['student_id']), 403);

        $event = CalendarEvent::findOrFail($data['calendar_event_id']);

        AssignmentExtension::create([
            'sid' => $tutor->sid,
            'calendar_event_id' => $event->id,
            'student_id' => $data['student_id'],
            'original_due' => $event->end,
            'extended_to' => $data['extended_to'],
            'reason' => $data['reason'],
            'granted_by' => $tutor->id,
        ]);

        return redirect()->route('admin.tutor.extensions.index')->with('success', 'Extension granted.');
    }
}
