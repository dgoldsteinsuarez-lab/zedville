<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Admin screen for the Low (Spending) Budget Activity position.
 * Extracted out of AdminEducationController (planning.md Phase 3 #4).
 *
 * Unlike its three siblings (Mba/EmergencyFund/HighBudgetActivity
 * PositionController), this one is NOT per-class: lba_position() reads
 * session('sid') instead of 'selected_school' (every sibling, and the
 * IsAdmin middleware, use 'selected_school' - 'sid' is never set to
 * anything here, so $sid is always null), and
 * updateOrInsert(['user_id' => $userId, 'id' => 4], ...) treats the
 * auto-increment primary key as if `id` 4 were a fixed "slot" for this
 * activity, rather than matching on activity_key/cid like the siblings
 * do. Preserved verbatim during the move rather than silently fixed -
 * see bugs.md.
 */
class LowBudgetActivityController extends Controller
{
    public function low_budget_activity()
    {
        $userId = Auth::id();

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

        $sid = session()->has('sid') ? session('sid') : null;

        DB::table('mba_position')->updateOrInsert(
            [
                'user_id' => $userId,
                'id' => 4,
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
