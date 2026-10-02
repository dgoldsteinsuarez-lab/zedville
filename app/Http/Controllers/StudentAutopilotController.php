<?php

namespace App\Http\Controllers;

use App\Models\AutopilotPeriod;
use Illuminate\Http\Request;

/**
 * A student can explicitly turn Autopilot on for themself (a visible option,
 * not a silent default - Admin Structure doc, Student mode). They can only end
 * periods they started themselves; periods set by a Tutor/admin stay in place.
 */
class StudentAutopilotController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isStudent(), 403);

        $data = $request->validate([
            'end_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . now()->addDays(90)->toDateString()],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        AutopilotPeriod::create([
            'sid' => $user->sid,
            'student_id' => $user->id,
            'class_id' => $user->grade,
            'start_date' => now()->toDateString(),
            'end_date' => $data['end_date'],
            'reason' => $data['reason'] ?: 'Requested by the student',
            'created_by' => $user->id,
            'status' => 'active',
        ]);

        return redirect(route('profile.edit') . '#tab8')->with('success', 'Autopilot is on.');
    }

    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        abort_unless($user->isStudent(), 403);

        AutopilotPeriod::where('student_id', $user->id)->where('created_by', $user->id)->findOrFail($id)
            ->update(['status' => 'cancelled']);

        return redirect(route('profile.edit') . '#tab8')->with('success', 'Autopilot is off.');
    }
}
