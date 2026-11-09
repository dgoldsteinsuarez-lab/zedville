<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ClassActivityAssigner;
use App\Support\AdminLevel;
use App\Support\AssignmentScope;
use App\Support\ClassLabel;
use App\Support\ClassColor;

/**
 * Daniel, 2026-10-21: "for the spending activity, i can not see all the
 * spending activities that are in the system, I can not select any, solve
 * it." Confirmed via AskUserQuestion: one page, with an Activity dropdown,
 * instead of two separate admin screens/nav links (HighBudgetActivity
 * Controller, still present and untouched - this is a new, additional
 * entry point, not a replacement of its underlying routes).
 *
 * Originally also covered "Low" (LowBudgetActivityController) the same
 * way - removed entirely the same day (Daniel: "you can remove low
 * spending activities from the system"), so this now only ever resolves
 * to the one High entry in ACTIVITIES. Kept as a map (not a bare constant
 * pair) in case a different activity type is added later.
 */
class SpendingActivityController extends Controller
{
    /** activity (form value) => [activity_key, calendar event title] */
    private const ACTIVITIES = [
        'high' => ['hba_position', 'Spending budget activity'],
        // Low Spending is back as its own kind (Daniel, 2026-10-31) - see Activity::SLOTS
        'low' => ['lba_position', 'Low spending activity'],
    ];

    public function index()
    {
        $userId = Auth::id();
        $sid = session('selected_school');

        $classes = ClassLabel::classes($sid);

        $list = DB::table('mba_position as mp')
            ->leftJoin('school_domains as sd', 'mp.sid', '=', 'sd.id')
            ->leftJoin('classes as c', function ($join) {
                $join->on('mp.cid', '=', 'c.id')
                    ->on('mp.sid', '=', 'c.sid');
            })
            ->leftJoin('grades as cg', 'cg.id', '=', 'c.grade_id')
            ->leftJoin('activities as act', 'mp.activity_id', '=', 'act.id')
            // Every class's assignment, whoever made it: a position belongs
            // to the class (2026-10-26).
            ->whereIn('mp.activity_key', ['hba_position', 'lba_position'])
            ->when($sid, function ($q) use ($sid) {
                $q->where('mp.sid', $sid);
            })
            // the level being worked on (the menu by level): its classes only
            ->when(AdminLevel::id(), fn ($q, $level) => $q->where('cg.grade_level_id', $level))
            ->select('mp.*', 'sd.school_name', 'sd.id as school_id', 'c.name as class_name', 'cg.name as class_grade_name', 'c.classCode', 'act.title as activity_title')
            ->orderByDesc('mp.id')
            ->get();

        $activities = AdminLevel::scope(Activity::query())->orderBy('type')->orderBy('number')->get();

        return view('admin.education.spending-activity', compact('classes', 'list', 'activities'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'activity_id' => ['required', 'integer', 'exists:activities,id'],
            'position' => ['required', 'in:required,optional'],
            'cid' => ['required'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $activityId = $request->input('activity_id');
        $activityRow = Activity::findOrFail($activityId);
        abort_unless(isset(self::ACTIVITIES[$activityRow->type]), 422, 'Unknown activity type.');
        [$activityKey, $calendarTitle] = self::ACTIVITIES[$activityRow->type];

        $userId = Auth::id();
        $position = $request->input('position');
        $cid = $request->input('cid');
        $id = $request->id;
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // "All classes" (of this school, or of every school): the same
        // assignment for each class, updating the ones that have it.
        if (AssignmentScope::isMany($cid)) {
            if ($id) {
                return back()->with('error', 'To give it to many classes, add it as a new assignment instead of editing this one.');
            }
            $classes = AssignmentScope::classes($cid, session('selected_school'));
            if ($classes->isEmpty()) {
                return back()->with('error', 'There are no classes to assign this to.');
            }
            foreach ($classes as $class) {
                ClassActivityAssigner::assign($class, [
                    'key' => $activityKey, 'activity_id' => $activityId, 'activitys' => true,
                    'title' => $calendarTitle, 'description' => 'This is ' . $calendarTitle,
                    'position' => $position, 'start_date' => $startDate, 'end_date' => $endDate,
                ]);
            }

            return back()->with('success', 'Spending Activity assigned to ' . $classes->count() . ' classes.');
        }

        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;

        // The class's own school: students only see an assignment of their
        // school, so one saved while managing "all schools" (no school)
        // never reached anyone (2026-10-26).
        $sid = session('selected_school') ?: DB::table('classes')->where('id', $cid)->value('sid');

        $duplicate = DB::table('mba_position')
            ->where('activity_key', $activityKey)
            ->where('cid', $cid)
            ->when($id, function ($q) use ($id) {
                $q->where('id', '!=', $id);
            })
            ->first();

        if ($duplicate) {
            return back()->with('error', 'This class already has this Spending Activity.');
        }

        if ($id) {
            DB::table('mba_position')
                ->where('id', $id)
                ->update([
                    'sid' => $sid,
                    'cid' => $cid,
                    'activity_key' => $activityKey,
                    'activity_id' => $activityId,
                    'required' => $required,
                    'optional' => $optional,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('mba_position')->insert([
                'user_id' => $userId,
                'activity_key' => $activityKey,
                'activity_id' => $activityId,
                'sid' => $sid,
                'cid' => $cid,
                'required' => $required,
                'optional' => $optional,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // The event belongs to the class's own school - saving while managing
        // "all schools" used to add a second copy of it (2026-10-25).
        $eventSid = $sid ?: DB::table('classes')->where('id', $cid)->value('sid');

        $calendarEvent = DB::table('calendar_events')
            ->where('sid', $eventSid)
            ->where('classId', $cid)
            ->where('title', $calendarTitle)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->first();

        // See MbaPositionController - FullCalendar's end is exclusive, +1
        // day so an admin-picked end_date is still shown as included.
        $calendarStart = $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : now();
        $calendarEnd = $endDate ? \Carbon\Carbon::parse($endDate)->addDay()->startOfDay() : now();

        if ($calendarEvent) {
            DB::table('calendar_events')
                ->where('id', $calendarEvent->id)
                ->update([
                    'position' => $position,
                    'activitys' => 1,
                    'backgroundColor' => ClassColor::hex((int) $cid),
                    'borderColor' => ClassColor::hex((int) $cid),
                    'start' => $calendarStart,
                    'end' => $calendarEnd,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('calendar_events')->insert([
                'sid' => $eventSid,
                'classId' => $cid,
                'title' => $calendarTitle,
                'description' => 'This is ' . $calendarTitle,
                'position' => $position,
                'activitys' => 1,
                'repeatActivity' => '0',
                'backgroundColor' => ClassColor::hex((int) $cid),
                'borderColor' => ClassColor::hex((int) $cid),
                'start' => $calendarStart,
                'end' => $calendarEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Spending Activity Position updated successfully!');
    }

    public function destroy($id)
    {
        // Any class's assignment the admin can see (the school being
        // managed, or every school for a super admin), whoever made it.
        $sid = session('selected_school') ?: (Auth::user()->isSuperAdmin() ? null : Auth::user()->sid);
        $position = DB::table('mba_position')
            ->where('id', $id)
            ->whereIn('activity_key', ['hba_position', 'lba_position'])
            ->when($sid, fn ($q) => $q->where('sid', $sid))
            ->first();

        if (!$position) {
            return back()->with('error', 'Record not found.');
        }

        [, $calendarTitle] = self::ACTIVITIES[$position->activity_key === 'lba_position' ? 'low' : 'high'];

        DB::table('calendar_events')
            ->where('sid', $position->sid)
            ->where('classId', $position->cid)
            ->where('title', $calendarTitle)
            ->delete();

        DB::table('mba_position')->where('id', $id)->delete();

        return back()->with('success', 'Spending Activity Position deleted successfully.');
    }
}
