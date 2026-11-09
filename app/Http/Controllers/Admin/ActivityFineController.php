<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityFine;
use App\Models\ActivityFineSetting;
use Illuminate\Http\Request;

/**
 * Bank > Activity Fines: the fines charged for Required activities not done
 * in time (App\Services\ActivityFineService, Daniel 2026-11-03), and what one
 * costs - which only a Super Admin changes (0 turns them off). A Tutor or
 * School Admin sees their own students' fines.
 */
class ActivityFineController extends Controller
{
    public function index(Request $request)
    {
        $ids = $request->user()->manageableStudentIds();

        $fines = ActivityFine::with(['student:id,name,nickname,grade', 'transaction:id,meta'])
            ->when($ids !== null, fn ($q) => $q->whereIn('student_id', $ids))
            ->latest('id')
            ->limit(500)
            ->get();

        return view('admin.activity-fines.index', [
            'fines' => $fines,
            'setting' => ActivityFineSetting::current(),
            'canEdit' => $request->user()->isSuperAdmin(),
        ]);
    }

    public function settings(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:0', 'max:10000']]);

        $setting = ActivityFineSetting::current();
        $setting->amount = round((float) $data['amount'], 2);
        $setting->updated_by = $request->user()->id;
        $setting->save();

        return back()->with('success', $setting->amount > 0
            ? 'Each Required activity not done in time now costs ' . number_format($setting->amount, 2) . ' zeds.'
            : 'Fines are off: nobody is fined for activities not done in time.');
    }
}
