<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Admin screen for the High (Spending) Budget Activity position: which
 * class has it required/optional, surfaced on the calendar. Extracted
 * out of AdminEducationController (planning.md Phase 3 #4) - fully
 * self-contained. Structurally identical to MbaPositionController,
 * just keyed by activity_key 'hba_position' / calendar title "Spending
 * budget activity".
 */
class HighBudgetActivityController extends Controller
{
    public function high_budget_activity()
    {
        $userId = Auth::id();
        $sid = session('selected_school');

        $positionQuery = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'hba_position');

        if ($sid) {
            $positionQuery->where('sid', $sid);
        } else {
            $positionQuery->whereNull('sid');
        }

        $position = $positionQuery->first();

        $classes = DB::table('classes')
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            })
            ->get();

        $mbaList = DB::table('mba_position as mp')
            ->leftJoin('school_domains as sd', 'mp.sid', '=', 'sd.id')
            ->leftJoin('classes as c', function ($join) {
                $join->on('mp.cid', '=', 'c.id')
                    ->on('mp.sid', '=', 'c.sid');
            })
            ->where('mp.user_id', $userId)
            ->where('mp.activity_key', 'hba_position')
            ->when($sid, function ($q) use ($sid) {
                $q->where('mp.sid', $sid);
            }, function ($q) {
                $q->whereNull('mp.sid');
            })
            ->select(
                'mp.*',
                'sd.school_name',
                'sd.id as school_id',
                'c.name as class_name',
                'c.classCode'
            )
            ->orderByDesc('mp.id')
            ->get();

        return view('admin.education.high-budget-activity', compact('position', 'classes', 'mbaList'));
    }

    public function hba_position(Request $request)
    {
        $userId = Auth::id();
        $position = $request->input('position');
        $cid = $request->input('cid');
        $id = $request->id;
        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;

        $sid = session()->has('selected_school') ? session('selected_school') : null;

        $duplicate = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'hba_position')
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
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('mba_position')->insert([
                'user_id' => $userId,
                'activity_key' => 'hba_position',
                'sid' => $sid,
                'cid' => $cid,
                'required' => $required,
                'optional' => $optional,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $calendarEvent = DB::table('calendar_events')
            ->where('sid', $sid)
            ->where('classId', $cid)
            ->where('title', 'Spending budget activity')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->first();

        if ($calendarEvent) {
            DB::table('calendar_events')
                ->where('id', $calendarEvent->id)
                ->update([
                    'position' => $position,
                    'backgroundColor' => '#0053f9',
                    'borderColor' => '#0053f9',
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('calendar_events')->insert([
                'sid' => $sid,
                'classId' => $cid,
                'title' => 'Spending budget activity',
                'description' => 'This is Spending budget activity',
                'position' => $position,
                'repeatActivity' => '0',
                'backgroundColor' => '#0053f9',
                'borderColor' => '#0053f9',
                'start' => now(),
                'end' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'High Budget Position updated successfully!');
    }
}
