<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Admin screen for MBA (Monthly Budget Activity) position: which class
 * has it required/optional, surfaced on the calendar. Extracted out of
 * AdminEducationController (planning.md Phase 3 #4) - fully
 * self-contained.
 */
class MbaPositionController extends Controller
{
    public function monthly_budget_activity()
    {
        $userId = Auth::id();
        $sid = session('selected_school');

        $positionQuery = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'mba');

        if ($sid) {
            // School selected
            $positionQuery->where('sid', $sid);
        } else {
            // No school selected
            $positionQuery->whereNull('sid');
        }

        $position = $positionQuery->first();

        // Show only classes of selected school
        $classes = DB::table('classes')
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            })
            ->get();
        // List
        $mbaList = DB::table('mba_position as mp')
            ->leftJoin('school_domains as sd', 'mp.sid', '=', 'sd.id')
            ->leftJoin('classes as c', function ($join) {
                $join->on('mp.cid', '=', 'c.id')
                    ->on('mp.sid', '=', 'c.sid');
            })
            ->where('mp.user_id', $userId)
            ->where('mp.activity_key', 'mba')
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

        return view('admin.education.monthly-budget-activity', compact('position', 'classes', 'mbaList'));
    }

    public function mba_position(Request $request)
    {
        $userId = Auth::id();
        $position = $request->input('position');
        $cid = $request->input('cid'); // Get selected class
        $id = $request->id;

        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;

        // ✅ Get sid from session or NULL
        $sid = session()->has('selected_school') ? session('selected_school') : null;

        /*
   |--------------------------------------------------------------------------
   | Check Duplicate
   |--------------------------------------------------------------------------
   | Don't allow same User + School + Class + Activity twice
   */
        $duplicate = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'mba')
            ->where('cid', $cid)
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            }, function ($q) {
                $q->whereNull('sid');
            })
            ->when($id, function ($q) use ($id) {
                // Ignore current record while updating
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
                'activity_key' => 'mba',
                'sid' => $sid,
                'cid' => $cid,
                'required' => $required,
                'optional' => $optional,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        //Insert Event under calander table
        $calendarEvent = DB::table('calendar_events')
            ->where('sid', $sid)
            ->where('classId', $cid)
            ->where('title', 'MBA')
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
                'classId' => $cid, // Save class here too if column exists
                'title' => 'MBA',
                'description' => 'This is MBA',
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

        return back()->with('success', 'MBA Position updated successfully!');
    }

    public function mba_position_delete($id)
    {
        $userId = Auth::id();

        $position = DB::table('mba_position')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$position) {
            return back()->with('error', 'Record not found.');
        }

        // Delete calendar event
        DB::table('calendar_events')
            ->where('sid', $position->sid)
            ->where('classId', $position->cid)
            ->where('title', 'MBA')
            ->delete();

        // Delete MBA position
        DB::table('mba_position')
            ->where('id', $id)
            ->delete();

        return back()->with('success', 'MBA Position deleted successfully.');
    }
}
