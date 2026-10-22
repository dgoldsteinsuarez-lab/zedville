<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            ->where('mp.activity_key', 'emeaccount')
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

        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;
        $sid = session()->has('selected_school') ? session('selected_school') : null;

        $duplicate = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'emeaccount')
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

        $calendarEvent = DB::table('calendar_events')
            ->where('sid', $sid)
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
                'sid' => $sid,
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
