<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutopilotPeriod;
use App\Models\Grade;
use App\Models\User;
use App\Services\AutopilotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Autopilot for students (approved absence). Per the Admin Roles & Permissions
 * doc: Tutor, School Admin and Super Admin can activate it, with a documented
 * reason and an end date. Each admin only reaches the students in their own
 * scope (Tutor = own classes, School Admin = own school, Super Admin = all).
 * Students can't turn it on themselves (Daniel, 2026-10-22).
 *
 * A period can cover one student or a whole Grade (e.g. long school
 * holidays). A whole-Grade period is one row per student in it, sharing a
 * batch_id, so it lists and cancels as one entry. It covers the students in
 * the Grade when it is saved; a student who joins later isn't added.
 *
 * A period forgotten can be set later, starting up to 2 months back
 * (Daniel, 2026-10-31); the months already gone are caught up when it's
 * saved (AutopilotService::catchUp). Cancelling a period doesn't charge
 * again what that gave back.
 */
class AutopilotController extends Controller
{
    private function scopedStudents()
    {
        $ids = Auth::user()->manageableStudentIds();
        $q = User::where('role', 4);
        return $ids === null ? $q : $q->whereIn('id', $ids);
    }

    /** Grades this admin can schedule for: Tutor = Grades of their own Classes, School Admin = own school. */
    private function scopedGrades()
    {
        $user = Auth::user();
        $q = Grade::query()->orderBy('name');

        if ($user->isSuperAdmin()) {
            return session('selected_school') ? $q->where('sid', session('selected_school')) : $q;
        }
        if ($user->isSchoolAdmin()) {
            return $q->where('sid', $user->sid);
        }

        return $q->whereIn('id', $user->tutoredClasses()->pluck('classes.grade_id'));
    }

    public function index()
    {
        $students = $this->scopedStudents()->orderBy('name')->get(['id', 'name', 'nickname', 'grade']);
        $grades = $this->scopedGrades()->withCount('classes')->get();

        $periods = AutopilotPeriod::with(['student:id,name,nickname', 'creator:id,nickname', 'grade:id,name'])
            ->whereIn('student_id', $students->pluck('id'))
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        // A whole-Grade period shows as one row with its student count.
        $rows = $periods->groupBy(fn ($p) => $p->batch_id ?: 'single-' . $p->id)
            ->map(function ($group) {
                $first = $group->first();
                $first->student_count = $group->count();
                $first->active_count = $group->where('status', 'active')->count();

                return $first;
            })
            ->values();

        $earliest = AutopilotService::earliestStart();

        return view('admin.autopilot.index', compact('students', 'grades', 'rows', 'earliest'));
    }

    public function store(Request $request)
    {
        $earliest = AutopilotService::earliestStart();
        $data = $request->validate([
            'target' => ['required', 'in:student,grade'],
            'student_id' => ['required_if:target,student', 'nullable', 'integer'],
            'grade_id' => ['required_if:target,grade', 'nullable', 'integer'],
            'start_date' => ['required', 'date', 'after_or_equal:' . $earliest->toDateString()],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'start_date.after_or_equal' => 'Autopilot can start at most ' . AutopilotService::MAX_MONTHS_BACK
                . ' months back: the earliest start date is ' . $earliest->toDateString() . '.',
        ]);

        $period = [
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'],
            'created_by' => Auth::id(),
            'status' => 'active',
        ];

        if ($data['target'] === 'student') {
            $student = $this->scopedStudents()->findOrFail($data['student_id']);

            $done = DB::transaction(fn () => AutopilotService::catchUp(AutopilotPeriod::create($period + [
                'sid' => $student->sid,
                'student_id' => $student->id,
                'class_id' => $student->grade,
            ]), Auth::id()));

            return redirect()->route('admin.autopilot.index')
                ->with('success', 'Autopilot recorded for ' . $student->nickname . '.' . $this->caughtUp($done));
        }

        $grade = $this->scopedGrades()->findOrFail($data['grade_id']);
        $students = $this->scopedStudents()
            ->whereIn('grade', $grade->classes()->pluck('id'))
            ->get(['id', 'sid', 'grade']);

        if ($students->isEmpty()) {
            return back()->withInput()->withErrors(['grade_id' => 'There are no students in ' . $grade->name . ' that you can put on Autopilot.']);
        }

        $batch = (string) Str::uuid();
        $done = DB::transaction(function () use ($students, $period, $grade, $batch) {
            $done = ['refunds' => 0, 'refunded' => 0.0, 'lines' => 0];
            foreach ($students as $student) {
                $one = AutopilotService::catchUp(AutopilotPeriod::create($period + [
                    'sid' => $student->sid,
                    'student_id' => $student->id,
                    'class_id' => $student->grade,
                    'grade_id' => $grade->id,
                    'batch_id' => $batch,
                ]), Auth::id());
                foreach ($done as $k => $v) {
                    $done[$k] = $v + $one[$k];
                }
            }

            return $done;
        });

        return redirect()->route('admin.autopilot.index')
            ->with('success', 'Autopilot scheduled for ' . $grade->name . ' (' . $students->count() . ' students).' . $this->caughtUp($done));
    }

    /** What a period set later did for the months already gone, for the success message. */
    private function caughtUp(array $done): string
    {
        $parts = [];
        if ($done['refunds'] > 0) {
            $parts[] = $done['refunds'] . ' ' . Str::plural('penalty', $done['refunds']) . ' given back (' . zeds($done['refunded']) . ')';
        }
        if ($done['lines'] > 0) {
            $parts[] = $done['lines'] . ' past ' . Str::plural('month', $done['lines']) . ' added to the history';
        }

        return $parts ? ' Earlier months caught up: ' . implode(', ', $parts) . '.' : '';
    }

    public function cancel($id)
    {
        $period = AutopilotPeriod::whereIn('student_id', $this->scopedStudents()->pluck('id'))->findOrFail($id);
        $period->update(['status' => 'cancelled']);

        return redirect()->route('admin.autopilot.index')->with('success', 'Autopilot period cancelled.');
    }

    /** Cancels every student's period in a whole-Grade schedule (within this admin's scope). */
    public function cancelBatch($batch)
    {
        $cancelled = AutopilotPeriod::where('batch_id', $batch)
            ->whereIn('student_id', $this->scopedStudents()->pluck('id'))
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        abort_if($cancelled === 0, 404);

        return redirect()->route('admin.autopilot.index')->with('success', 'Autopilot cancelled for ' . $cancelled . ' students.');
    }

    /**
     * A Tutor switching their own citizen account's Autopilot on/off (default
     * on). Distinct from AutopilotPeriod, which models a dated absence for a
     * student — this is a standing preference for the Tutor themself.
     */
    public function toggleSelf(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isTutor(), 403);

        $user->update(['autopilot_enabled' => $request->boolean('autopilot_enabled')]);

        return back()->with('success', 'Autopilot is now ' . ($user->autopilot_enabled ? 'on' : 'off') . '.');
    }
}
