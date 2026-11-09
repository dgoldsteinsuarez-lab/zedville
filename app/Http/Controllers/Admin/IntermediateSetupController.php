<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\IntermediateClassService;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin > Intermediate > Intermediate setup: the month each Intermediate class
 * started the level (the start of the school year unless an admin sets
 * another), which its months count from, and each student's scenario - set on
 * its own from their bank (Daniel, 2026-11-08: "debería hacerse por estudiante,
 * esto puede ser mucho trabajo"): A with a fund from the Rookie, B without.
 */
class IntermediateSetupController extends Controller
{
    public function index()
    {
        $sid = $this->school();
        $intermediate = GradeLevel::where('sort_order', Level::INTERMEDIATE)->value('id');

        $classes = SchoolClass::with('grade')
            ->when($sid, fn ($q) => $q->where('sid', $sid))
            ->whereHas('grade', fn ($g) => $g->where('grade_level_id', $intermediate))
            ->orderBy('name')->get()
            ->each(function (SchoolClass $class) {
                $class->level_start = IntermediateClassService::start($class);
                $class->level_month = IntermediateClassService::month($class);
                $class->scenarios = User::where('role', 4)->where('grade', $class->id)->orderBy('name')->get()
                    ->groupBy(fn (User $student) => IntermediateClassService::scenarioFor($student, $class));
            });

        return view('admin.intermediate.setup', compact('classes'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $sid = $this->school();
        abort_if($sid && (string) $class->sid !== (string) $sid, 404);
        $data = $request->validate(['intermediate_started_on' => ['required', 'date_format:Y-m']]);

        $class->intermediate_started_on = Carbon::createFromFormat('!Y-m', $data['intermediate_started_on']);
        $class->save();

        return back()->with('success', $class->name . ' started the Intermediate in ' . $class->intermediate_started_on->format('F Y') . '.');
    }

    /** The school being managed; a school admin's own. */
    private function school()
    {
        return session('selected_school') ?: (Auth::user()->isSuperAdmin() ? null : Auth::user()->sid);
    }
}
