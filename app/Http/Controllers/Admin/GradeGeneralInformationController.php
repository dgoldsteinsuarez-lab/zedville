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

        return view('admin.grades.general-information', compact('grade', 'school', 'levels', 'monthSetting'));
    }
}
