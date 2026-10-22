<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    ];

    public function index()
    {
        $userId = Auth::id();
        $sid = session('selected_school');

        $classes = DB::table('classes')
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            })
            ->get();

        $list = DB::table('mba_position as mp')
            ->leftJoin('school_domains as sd', 'mp.sid', '=', 'sd.id')
            ->leftJoin('classes as c', function ($join) {
                $join->on('mp.cid', '=', 'c.id')
                    ->on('mp.sid', '=', 'c.sid');
            })
            ->leftJoin('activities as act', 'mp.activity_id', '=', 'act.id')
            ->where('mp.user_id', $userId)
            ->where('mp.activity_key', 'hba_position')
            ->when($sid, function ($q) use ($sid) {
                $q->where('mp.sid', $sid);
            }, function ($q) {
                $q->whereNull('mp.sid');
            })
            ->select('mp.*', 'sd.school_name', 'sd.id as school_id', 'c.name as class_name', 'c.classCode', 'act.title as activity_title')
            ->orderByDesc('mp.id')
            ->get();

        $activities = Activity::orderBy('type')->orderBy('number')->get();

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

        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;

        $sid = session()->has('selected_school') ? session('selected_school') : null;

        $duplicate = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', $activityKey)
            ->where('cid', $cid)
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            }, function ($q) {
                $q->whereNull('sid');
            })
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

        $calendarEvent = DB::table('calendar_events')
            ->where('sid', $sid)
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
                'sid' => $sid,
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
        $userId = Auth::id();

        $position = DB::table('mba_position')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->where('activity_key', 'hba_position')
            ->first();

        if (!$position) {
            return back()->with('error', 'Record not found.');
        }

        [, $calendarTitle] = self::ACTIVITIES['high'];

        DB::table('calendar_events')
            ->where('sid', $position->sid)
            ->where('classId', $position->cid)
            ->where('title', $calendarTitle)
            ->delete();

        DB::table('mba_position')->where('id', $id)->delete();

        return back()->with('success', 'Spending Activity Position deleted successfully.');
    }
}
