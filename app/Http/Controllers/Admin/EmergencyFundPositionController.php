<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ClassActivityAssigner;
use App\Support\AdminLevel;
use App\Support\AssignmentScope;
use App\Support\ClassLabel;
use App\Support\ClassColor;

/**
 * Admin screen for the Emergency Fund Account activity position: which
 * class has it required/optional, surfaced on the calendar. Extracted
 * out of AdminEducationController (planning.md Phase 3 #4) - fully
 * self-contained. Structurally identical to MbaPositionController,
 * just keyed by activity_key 'emeaccount' / calendar title "Emergency
 * Fund" instead of 'mba' / "MBA".
 */
class EmergencyFundPositionController extends Controller
{
    public function emergencyFundAccount()
    {
        $userId = Auth::id();
        $sid = session('selected_school');

        $positionQuery = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'emeaccount');

        if ($sid) {
            $positionQuery->where('sid', $sid);
        } else {
            $positionQuery->whereNull('sid');
        }

        $position = $positionQuery->first();

        $classes = ClassLabel::classes($sid);

        $mbaList = DB::table('mba_position as mp')
            ->leftJoin('school_domains as sd', 'mp.sid', '=', 'sd.id')
            ->leftJoin('classes as c', function ($join) {
                $join->on('mp.cid', '=', 'c.id')
                    ->on('mp.sid', '=', 'c.sid');
            })
            ->leftJoin('grades as cg', 'cg.id', '=', 'c.grade_id')
            // Every class's assignment, whoever made it: a position belongs
            // to the class (2026-10-26).
            ->where('mp.activity_key', 'emeaccount')
            ->when($sid, function ($q) use ($sid) {
                $q->where('mp.sid', $sid);
            })
            // the level being worked on (the menu by level): its classes only
            ->when(AdminLevel::id(), fn ($q, $level) => $q->where('cg.grade_level_id', $level))
            ->select(
                'mp.*',
                'sd.school_name',
                'sd.id as school_id',
                'c.name as class_name',
                'cg.name as class_grade_name',
                'c.classCode'
            )
            ->orderByDesc('mp.id')
            ->get();

        return view('admin.education.emergency-fund-account-position', compact('position', 'classes', 'mbaList'));
    }

    public function emergencyFundPosition(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

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
                    'key' => 'emeaccount', 'title' => 'Emergency Fund', 'description' => 'This is Emergency Fund',
                    'position' => $position, 'start_date' => $startDate, 'end_date' => $endDate,
                ]);
            }

            return back()->with('success', 'Emergency Fund assigned to ' . $classes->count() . ' classes.');
        }

        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;
        // The class's own school: students only see an assignment of their
        // school, so one saved while managing "all schools" (no school)
        // never reached anyone (2026-10-26).
        $sid = session('selected_school') ?: DB::table('classes')->where('id', $cid)->value('sid');

        $duplicate = DB::table('mba_position')
            ->where('activity_key', 'emeaccount')
            ->where('cid', $cid)
            ->when($id, function ($q) use ($id) {
                $q->where('id', '!=', $id);
            })
            ->first();

        if ($duplicate) {
            return back()->with('error', 'This class already has an MBA Position.');
        }

        if ($id) {
            DB::table('mba_position')
                ->where('id', $id)
                ->update([
                    'sid' => $sid,
                    'cid' => $cid,
                    'required' => $required,
                    'optional' => $optional,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('mba_position')->insert([
                'user_id' => $userId,
                'activity_key' => 'emeaccount',
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
            ->where('title', 'Emergency Fund')
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
                'title' => 'Emergency Fund',
                'description' => 'This is Emergency Fund',
                'position' => $position,
                'repeatActivity' => '0',
                'backgroundColor' => ClassColor::hex((int) $cid),
                'borderColor' => ClassColor::hex((int) $cid),
                'start' => $calendarStart,
                'end' => $calendarEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Emergency Fund Account Position updated successfully!');
    }
}
