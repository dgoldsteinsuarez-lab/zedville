<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ClassActivityAssigner;
use App\Services\DonationService;
use App\Support\AdminLevel;
use App\Support\AssignmentScope;
use App\Support\ClassLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin screen for the Monthly Donation activity (Daniel, 2026-11-08: "Añádelo
 * como actividad en Admin"): which class has it, required or optional, and its
 * days - on the calendar like the Emergency Fund (same mba_position rows, key
 * 'donation'; one class or many through ClassActivityAssigner). A student has
 * done it once their standing order to a charity is set up in the bank
 * (DonationService); a required one not done in time brings the City Hall fine.
 */
class DonationPositionController extends Controller
{
    public function index()
    {
        $sid = session('selected_school');
        $classes = ClassLabel::classes($sid);

        $list = DB::table('mba_position as mp')
            ->leftJoin('school_domains as sd', 'mp.sid', '=', 'sd.id')
            ->leftJoin('classes as c', function ($join) {
                $join->on('mp.cid', '=', 'c.id')->on('mp.sid', '=', 'c.sid');
            })
            ->leftJoin('grades as cg', 'cg.id', '=', 'c.grade_id')
            ->where('mp.activity_key', DonationService::KEY)
            ->when($sid, fn ($q) => $q->where('mp.sid', $sid))
            // the level being worked on (the menu by level): its classes only
            ->when(AdminLevel::id(), fn ($q, $level) => $q->where('cg.grade_level_id', $level))
            ->select('mp.*', 'sd.school_name', 'c.name as class_name', 'cg.name as class_grade_name')
            ->orderByDesc('mp.id')
            ->get();

        return view('admin.education.donation-position', compact('classes', 'list'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cid' => ['required', 'string'],
            'position' => ['required', 'in:required,optional'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $classes = AssignmentScope::isMany($data['cid'])
            ? AssignmentScope::classes($data['cid'], session('selected_school'))
            : DB::table('classes')->where('id', (int) $data['cid'])->get(['id', 'sid']);
        if ($classes->isEmpty()) {
            return back()->with('error', 'There are no classes to assign this to.');
        }

        foreach ($classes as $class) {
            ClassActivityAssigner::assign($class, [
                'key' => DonationService::KEY,
                'title' => DonationService::ACTIVITY,
                'description' => 'Set up the monthly donation to a charity in the bank',
                'position' => $data['position'],
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
            ]);
        }

        return back()->with('success', $classes->count() === 1
            ? 'Monthly Donation saved for the class.'
            : 'Monthly Donation assigned to ' . $classes->count() . ' classes.');
    }
}
