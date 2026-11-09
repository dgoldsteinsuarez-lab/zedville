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
 * updateGrade, the school-domain screen) rather than duplicating them — this
 * page is a relocated/consolidated view. Only the Grade's language and start
 * month (with the school's start month next to it) are saved here.
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
        $schoolStart = SchoolMonthSetting::schoolStartMonth($grade->sid);
        $progressionMonth = SchoolMonthSetting::progressionMonth($grade->start_month ?: $schoolStart);

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

        return view('admin.grades.general-information', compact('grade', 'school', 'levels', 'schoolStart', 'progressionMonth', 'canSeeCityProposals', 'cityProposals'));
    }

    /**
     * The Grade's start month (Daniel, 2026-10-31: "i should be able to
     * choose the month that a grade starts, because it can be independent
     * from the school") - Month 1 of its students' questions. Empty: the
     * school's.
     */
    public function updateStartMonth(Request $request, $id)
    {
        $grade = $this->grade($id);
        $request->validate(['start_month' => 'nullable|integer|between:1,12']);

        $grade->update(['start_month' => $request->filled('start_month') ? (int) $request->start_month : null]);

        return $this->backToStartMonth($grade, $grade->start_month
            ? $grade->name . ' starts in ' . date('F', mktime(0, 0, 0, $grade->start_month, 1)) . '.'
            : $grade->name . ' follows the school’s start month.');
    }

    /** The school's start month, for every Grade there without its own. */
    public function updateSchoolStartMonth(Request $request, $id)
    {
        $grade = $this->grade($id);
        $request->validate(['school_start_month' => 'required|integer|between:1,12']);

        SchoolMonthSetting::updateOrCreate(['school_id' => $grade->sid], ['start_month' => (int) $request->school_start_month]);

        return $this->backToStartMonth($grade, 'The school starts in ' . date('F', mktime(0, 0, 0, (int) $request->school_start_month, 1)) . '.');
    }

    /**
     * The Grade's language (Daniel, 2026-10-22): saved on the Grade, for
     * Classes added later, and copied to every Class in it now. A Tutor can
     * change their own Class afterwards (Tutor\ClassLanguageController).
     */
    public function updateLanguage(Request $request, $id)
    {
        $grade = $this->grade($id);

        $request->validate([
            'default_language' => ['required', \Illuminate\Validation\Rule::in(array_keys(config('zedville.languages')))],
            'language_mode' => 'nullable|in:free,locked',
            'language_level' => 'required|in:standard,beginner,hybrid',
        ]);

        $settings = [
            'language_mode' => $request->input('language_mode', 'free'),
            'default_language' => $request->input('default_language'),
            'language_level' => $request->input('language_level'),
        ];

        $grade->update($settings);
        $classes = $grade->classes()->update($settings);

        return redirect(route('admin.grades.general-information', $grade->id) . '#grade-language')
            ->with('success', 'Language saved for ' . $grade->name . ' (' . $classes . ' ' . \Illuminate\Support\Str::plural('class', $classes) . ').');
    }

    /** One Grade of the admin's own school (any school for a Super Admin). */
    private function grade($id): Grade
    {
        $user = Auth::user();

        return Grade::when(!$user->isSuperAdmin(), fn ($q) => $q->where('sid', $user->sid))->findOrFail($id);
    }

    /** Back to the Start month card, with what it means this month. */
    private function backToStartMonth(Grade $grade, string $saved)
    {
        $month = SchoolMonthSetting::progressionMonth(SchoolMonthSetting::startMonthForGrade($grade));

        return redirect(route('admin.grades.general-information', $grade->id) . '#start-month')
            ->with('start_month_saved', $saved . ($month ? ' ' . now()->format('F') . ' is Month ' . $month . ' for ' . $grade->name . '.' : ''));
    }
}
