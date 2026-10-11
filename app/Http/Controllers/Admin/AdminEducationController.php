<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Grade;
use App\Models\Mascot;
use App\Models\SchoolDomain;

class AdminEducationController extends Controller
{
    public function index()
    {
        //return view('admin.education.index');
    }
    public function monthly_budget_activity()
    {
        $userId = Auth::id();
        $sid = session('selected_school');

        /*$position = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('id', 1)
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            })
            ->first();*/

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
                'sd.id as school_id', // <-- Fix here
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
        /*DB::table('mba_position')->updateOrInsert(
            [
                'user_id' => $userId,
                //'id'      => 1,
                'sid'     => $sid,
                'cid'     => $cid,
            ],
            [
                'activity_key' => 'mba',
                'required'     => $required,
                'optional'     => $optional,
                'sid'          => $sid,
                'cid'          => $cid,
                'updated_at'   => now(),
            ]
        );*/
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
        /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
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

            /*
            |--------------------------------------------------------------------------
            | Insert
            |--------------------------------------------------------------------------
            */

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

    public function emergencyFundAccount()
    {
        $userId = Auth::id();
        $sid = session('selected_school');
        /*$position = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('id', 2)
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            })
            ->first();*/
        $positionQuery = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'emeaccount');

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
            ->where('mp.activity_key', 'emeaccount')
            ->when($sid, function ($q) use ($sid) {
                $q->where('mp.sid', $sid);
            }, function ($q) {
                $q->whereNull('mp.sid');
            })
            ->select(
                'mp.*',
                'sd.school_name',
                'sd.id as school_id', // <-- Fix here
                'c.name as class_name',
                'c.classCode'
            )
            ->orderByDesc('mp.id')
            ->get();
        return view('admin.education.emergency-fund-account-position', compact('position', 'classes', 'mbaList'));
    }

    public function emergencyFundPosition(Request $request)
    {
        $userId = Auth::id();
        $position = $request->input('position');
        $cid = $request->input('cid'); // Get selected class
        $id = $request->id;

        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;
        // ✅ Get sid from session or NULL
        $sid = session()->has('selected_school') ? session('selected_school') : null;
        /*DB::table('mba_position')->updateOrInsert(
            [
                'user_id' => $userId,
                'id'      => 2, 
                'sid'     => $sid,
                'cid'     => $cid,      // ✅ Emergency Fund
            ],
            [
                'activity_key' => 'emeaccount',
                'required'   => $required,
                'optional'   => $optional,
                'sid'          => $sid,
                'cid'          => $cid,
                'updated_at' => now(),
                'created_at' => now()
            ]
        );*/
        /*
  |--------------------------------------------------------------------------
  | Check Duplicate
  |--------------------------------------------------------------------------
  | Don't allow same User + School + Class + Activity twice
  */
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
                // Ignore current record while updating
                $q->where('id', '!=', $id);
            })
            ->first();

        if ($duplicate) {
            return back()->with('error', 'This class already has an MBA Position.');
        }
        /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
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

            /*
            |--------------------------------------------------------------------------
            | Insert
            |--------------------------------------------------------------------------
            */

            DB::table('mba_position')->insert([
                'user_id' => $userId,
                'activity_key' => 'emeaccount',
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
            ->where('title', 'Emergency Fund')
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
                'title' => 'Emergency Fund',
                'description' => 'This is Emergency Fund',
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
        return back()->with('success', 'Emergency Fund Account Position updated successfully!');
    }

    public function high_budget_activity()
    {
        $userId = Auth::id();
        $sid = session('selected_school');

        /*$position = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('id', 3)
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            })
            ->first();*/
        $positionQuery = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'hba_position');

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
            ->where('mp.activity_key', 'hba_position')
            ->when($sid, function ($q) use ($sid) {
                $q->where('mp.sid', $sid);
            }, function ($q) {
                $q->whereNull('mp.sid');
            })
            ->select(
                'mp.*',
                'sd.school_name',
                'sd.id as school_id', // <-- Fix here
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
        $cid = $request->input('cid'); // Get selected class
        $id = $request->id;
        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;

        // ✅ Get sid from session or NULL
        $sid = session()->has('selected_school') ? session('selected_school') : null;

        /*DB::table('mba_position')->updateOrInsert(
            [
                'user_id' => $userId,
                'id'      => 3,        // ✅ High Budget Activity
                'sid'     => $sid,
                'cid'     => $cid,
            ],
            [
                'activity_key' => 'hba_position',
                'required'   => $required,
                'optional'   => $optional,
                'sid'        => $sid,
                'cid'          => $cid,
                'updated_at' => now(),
                'created_at' => now()
            ]
        );*/
        /*
   |--------------------------------------------------------------------------
   | Check Duplicate
   |--------------------------------------------------------------------------
   | Don't allow same User + School + Class + Activity twice
   */
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
                // Ignore current record while updating
                $q->where('id', '!=', $id);
            })
            ->first();

        if ($duplicate) {
            return back()->with('error', 'This class already has an MBA Position.');
        }
        /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
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

            /*
            |--------------------------------------------------------------------------
            | Insert
            |--------------------------------------------------------------------------
            */

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
                'classId' => $cid, // Save class here too if column exists
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
    public function low_budget_activity()
    {
        $userId = Auth::id();
        $sid = session('selected_school');

        $position = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('id', 4)
            ->first();

        return view('admin.education.low-budget-activity', compact('position'));
    }
    public function lba_position(Request $request)
    {
        $userId = Auth::id();
        $position = $request->input('position');

        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;

        // ✅ Get sid from session or NULL
        $sid = session()->has('sid') ? session('sid') : null;

        DB::table('mba_position')->updateOrInsert(
            [
                'user_id' => $userId,
                'id' => 4,        // ✅ Low Budget Activity
            ],
            [
                'activity_key' => 'lba_position',
                'required' => $required,
                'optional' => $optional,
                'updated_at' => now(),
                'created_at' => now()
            ]
        );
        $calendarEvent = DB::table('calendar_events')
            ->where('sid', $sid)
            ->where('title', 'Spending Low activity')
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
                'title' => 'Spending Low activity',
                'description' => 'This is Low budget activity',
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

        return back()->with('success', 'Low Budget Position updated successfully!');
    }
}
