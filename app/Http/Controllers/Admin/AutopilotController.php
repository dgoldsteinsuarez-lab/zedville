<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutopilotPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Autopilot for students (approved absence). Per the Admin Roles & Permissions
 * doc: Tutor, School Admin and Super Admin can activate it, with a documented
 * reason and an end date. Each admin only reaches the students in their own
 * scope (Tutor = own classes, School Admin = own school, Super Admin = all).
 */
class AutopilotController extends Controller
{
    private function scopedStudents()
    {
        $ids = Auth::user()->manageableStudentIds();
        $q = User::where('role', 4);
        return $ids === null ? $q : $q->whereIn('id', $ids);
    }

    public function index()
    {
        $students = $this->scopedStudents()->orderBy('name')->get(['id', 'name', 'nickname', 'grade']);

        $periods = AutopilotPeriod::with(['student:id,name,nickname', 'creator:id,nickname'])
            ->whereIn('student_id', $students->pluck('id'))
            ->orderByDesc('start_date')
            ->get();

        return view('admin.autopilot.index', compact('students', 'periods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $student = $this->scopedStudents()->findOrFail($data['student_id']);

        AutopilotPeriod::create([
            'sid' => $student->sid,
            'student_id' => $student->id,
            'class_id' => $student->grade,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'],
            'created_by' => Auth::id(),
            'status' => 'active',
        ]);

        return redirect()->route('admin.autopilot.index')->with('success', 'Autopilot recorded for ' . $student->nickname . '.');
    }

    public function cancel($id)
    {
        $period = AutopilotPeriod::whereIn('student_id', $this->scopedStudents()->pluck('id'))->findOrFail($id);
        $period->update(['status' => 'cancelled']);

        return redirect()->route('admin.autopilot.index')->with('success', 'Autopilot period cancelled.');
    }
}
