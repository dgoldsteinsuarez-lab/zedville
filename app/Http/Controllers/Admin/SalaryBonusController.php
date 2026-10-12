<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryBonus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin Roles & Permissions doc §2.3/§4.2: a Tutor or Super Admin can grant
 * a one-time or recurring salary bonus with a mandatory reason; amount and
 * frequency are editable (by cancelling and re-granting - this codebase's
 * append-only ledger convention means we don't edit a bonus in place once
 * any application has run against it), and a bonus can be paused without
 * deleting it. This controller only manages the *definition*; the actual
 * credit into Transaction1 happens in the `bonuses:apply` scheduled command.
 */
class SalaryBonusController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $ids = $user->manageableStudentIds();

        $bonuses = SalaryBonus::with(['student:id,name,nickname', 'grantedBy:id,nickname'])
            ->when($ids !== null, fn ($q) => $q->whereIn('student_id', $ids))
            ->latest()
            ->get();

        $students = User::where('role', 4)
            ->when($ids !== null, fn ($q) => $q->whereIn('id', $ids))
            ->orderBy('name')->get(['id', 'name', 'nickname']);

        return view('admin.salary-bonuses.index', compact('bonuses', 'students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'type' => ['nullable', 'string', 'max:50'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'frequency' => ['nullable', 'in:weekly,monthly'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $student = User::where('role', 4)->findOrFail($data['student_id']);
        abort_unless(Auth::user()->canManageStudent($student->id), 403);

        SalaryBonus::create([
            'sid' => $student->sid,
            'student_id' => $student->id,
            'granted_by' => Auth::id(),
            'amount' => $data['amount'],
            'type' => $data['type'] ?? 'Bonus',
            'reason' => $data['reason'],
            'frequency' => $data['frequency'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'status' => 'active',
        ]);

        return back()->with('success', 'Bonus granted - it is applied by the next daily run.');
    }

    public function pause(Request $request, $id)
    {
        $bonus = $this->findManageable($request, $id);
        $bonus->update(['status' => 'paused']);
        return back()->with('success', 'Bonus paused.');
    }

    public function resume(Request $request, $id)
    {
        $bonus = $this->findManageable($request, $id);
        $bonus->update(['status' => 'active']);
        return back()->with('success', 'Bonus resumed.');
    }

    public function cancel(Request $request, $id)
    {
        $bonus = $this->findManageable($request, $id);
        $bonus->update(['status' => 'cancelled']);
        return back()->with('success', 'Bonus cancelled.');
    }

    private function findManageable(Request $request, $id): SalaryBonus
    {
        $bonus = SalaryBonus::findOrFail($id);
        abort_unless($request->user()->canManageStudent($bonus->student_id), 403);
        return $bonus;
    }
}
