<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * CRUD for the real Grade entity (e.g. "Grade 8") — name + assigned Level,
 * shared by every Class underneath it. Introduced by the Grade/Class split;
 * Classes themselves (class code, Tutor, roster) are still managed at
 * AdminDashboardController::grade()/addGrade()/updateGrade()/deleteGrade(),
 * now reworked to point a Class at one of these Grades via `grade_id`.
 */
class GradeController extends Controller
{
    private function schoolScoped($query)
    {
        if (session()->has('selected_school')) {
            $query->where('sid', session('selected_school'));
        }
        return $query;
    }

    public function index(Request $request)
    {
        $sid = session('selected_school');
        if ($sid === null) {
            return redirect()->route('admin.dashboard')->with('error', 'Select a school first - use the banner at the top of the page.');
        }

        $grades = $this->schoolScoped(Grade::with(['level', 'classes']))
            ->orderBy('name')
            ->get();

        // Levels are a fixed global set (Rookie/Intermediate/Advance/Advance
        // Economist), same for every school - only which Level a given
        // Grade is assigned is per-school.
        $levels = GradeLevel::orderBy('sort_order')->get();
        $schoolStart = \App\Models\SchoolMonthSetting::schoolStartMonth($sid);

        return view('admin.grades.index', compact('grades', 'levels', 'schoolStart'));
    }

    public function store(Request $request)
    {
        $sid = session('selected_school');
        if ($sid === null) {
            return redirect()->route('admin.dashboard')->with('error', 'Select a school first - use the banner at the top of the page.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('grades', 'name')->where('sid', (string) $sid)],
            'grade_level_id' => 'nullable|integer',
            'default_language' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(config('zedville.languages')))],
            'language_mode' => 'nullable|in:free,locked',
            'language_level' => 'nullable|in:standard,beginner,hybrid',
            'start_month' => 'nullable|integer|between:1,12',
        ]);

        // Daniel, 2026-10-25: the language is picked right here (he couldn't
        // find where to make a Spanish class), and the new Grade opens with
        // its "Add Class" form ready - its Classes start in this language.
        $grade = Grade::create([
            'sid' => $sid,
            'name' => $request->name,
            'grade_level_id' => $this->validLevelId($request->grade_level_id),
            'default_language' => $request->input('default_language', 'en'),
            'language_mode' => $request->input('language_mode', 'free'),
            // and its level (Daniel, 2026-10-31): Beginner A2/B1 or Hybrid
            'language_level' => $request->input('language_level', 'standard'),
            // and the month it starts, if not the school's (Daniel, 2026-10-31)
            'start_month' => $request->filled('start_month') ? (int) $request->start_month : null,
        ]);

        return redirect(route('admin.grades.general-information', $grade->id) . '#add-class')
            ->with('success', "Grade \"{$grade->name}\" added. Now add its first Class.");
    }

    public function update(Request $request, $id)
    {
        $grade = $this->schoolScoped(Grade::query())->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('grades', 'name')->where('sid', (string) $grade->sid)->ignore($grade->id)],
            'grade_level_id' => 'nullable|integer',
        ]);

        $grade->update([
            'name' => $request->name,
            'grade_level_id' => $this->validLevelId($request->grade_level_id),
        ]);

        return redirect()->back()->with('success', 'Grade updated successfully!');
    }

    public function destroy($id)
    {
        $grade = $this->schoolScoped(Grade::query())->findOrFail($id);

        if ($grade->classes()->exists()) {
            return redirect()->back()->with('error', 'This Grade still has Classes under it. Move or delete them first.');
        }

        $grade->delete();

        return redirect()->back()->with('success', 'Grade deleted successfully!');
    }

    private function validLevelId($levelId)
    {
        if (empty($levelId)) {
            return null;
        }
        // Levels are global now - any school may assign any of the 4.
        $level = GradeLevel::find($levelId);
        abort_unless($level, 422, 'Grade level does not exist.');
        return $level->id;
    }
}
