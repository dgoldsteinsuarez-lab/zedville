<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolDomain;
use App\Models\SchoolMonthSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "General Information" page for one Grade — per the Admin Structure
 * Proposal doc §3: school info, assigned Level, School Month Settings
 * (moved here from Education), and the Classes-within-this-Grade table.
 *
 * Everything here reuses existing CRUD endpoints (AdminDashboardController's
 * updateGrade/bulkSaveSMS, the school-domain screen) rather than duplicating
 * them — this page is a relocated/consolidated view, not new business logic.
 */
class GradeGeneralInformationController extends Controller
{
    public function show(Request $request, $id)
    {
        $user = Auth::user();

        $grade = Grade::with(['level', 'classes.tutors', 'classes' => fn ($q) => $q->withCount('students')])
            ->when(!$user->isSuperAdmin(), fn ($q) => $q->where('sid', $user->sid))
            ->findOrFail($id);

        $school = SchoolDomain::find($grade->sid);
        // Levels are a fixed global set (Rookie/Intermediate/Advance/Advance
        // Economist) shared by every school - see bugs.md 2026-10-19.
        $levels = GradeLevel::orderBy('sort_order')->get();
        $monthSetting = SchoolMonthSetting::where('school_id', $grade->sid)->first();

        // City Proposals (students voting on a city name/quote change) moved
        // here from its own global top-level nav item - it's a minor, purely
        // Grade-scoped feature, not something that needs its own section of
        // the admin sidebar. Same access rule as the old standalone page
        // (routes/admin.php: role:super_admin,tutor) - School Admin never
        // saw this, so it stays hidden here too; a Tutor only sees it for a
        // Grade they actually have a Class in.
        $canSeeCityProposals = $user->isSuperAdmin()
            || ($user->isTutor() && $user->tutoredClasses()->where('classes.grade_id', $grade->id)->exists());

        $cityProposals = $canSeeCityProposals
            ? \App\Models\CityProposal::with(['proposer:id,name,nickname'])
                ->where('grade_id', $grade->id)
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('admin.grades.general-information', compact('grade', 'school', 'levels', 'monthSetting', 'canSeeCityProposals', 'cityProposals'));
    }
}
