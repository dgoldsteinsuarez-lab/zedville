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
        abort_if($sid === null, 422, 'Select a school first.');

        $grades = $this->schoolScoped(Grade::with(['level', 'classes']))
            ->orderBy('name')
            ->get();

        // Levels are a fixed global set (Rookie/Intermediate/Advance/Advance
        // Economist), same for every school - only which Level a given
        // Grade is assigned is per-school.
        $levels = GradeLevel::orderBy('sort_order')->get();

        return view('admin.grades.index', compact('grades', 'levels'));
    }

    public function store(Request $request)
    {
        $sid = session('selected_school');
        abort_if($sid === null, 422, 'Select a school first.');

        $request->validate([
            'name' => ['required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('grades', 'name')->where('sid', (string) $sid)],
            'grade_level_id' => 'nullable|integer',
        ]);

        Grade::create([
            'sid' => $sid,
            'name' => $request->name,
            'grade_level_id' => $this->validLevelId($request->grade_level_id),
        ]);

        return redirect()->back()->with('success', 'Grade added successfully!');
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
