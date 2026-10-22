<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\PisaProficiencyLevel;
use Illuminate\Http\Request;

/**
 * ZEDVILLE — PISA
 *
 * Admin screen for PISA's own official proficiency/difficulty levels
 * (currently seeded with Financial Literacy's 6 bands) - a global
 * reference catalog Daniel plans to build PISA-practice assignments
 * against later. Separate from Grade Levels (GradeLevelController-style
 * methods on AdminDashboardController) - "PISA is the same for all
 * levels," not nested under any one of them (bugs.md, 2026-10-20).
 *
 * Super-Admin-only, same reasoning as Grade Levels: this is a shared,
 * global standard, not a per-school setting a School Admin should be
 * able to redefine.
 */
class PisaLevelController extends Controller
{
    public function index()
    {
        $levels = PisaProficiencyLevel::orderBy('domain')->orderBy('sort_order')->get();

        return view('admin.pisa.index', compact('levels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'sort_order' => 'required|integer|min:1',
            'min_score' => 'nullable|numeric|min:0|max:1000',
            'max_score' => 'nullable|numeric|min:0|max:1000',
            'description' => 'nullable|string',
        ]);

        PisaProficiencyLevel::create($validated);

        return back()->with('success', 'PISA level added.');
    }

    public function update(Request $request, PisaProficiencyLevel $pisaLevel)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'sort_order' => 'required|integer|min:1',
            'min_score' => 'nullable|numeric|min:0|max:1000',
            'max_score' => 'nullable|numeric|min:0|max:1000',
            'description' => 'nullable|string',
        ]);

        $pisaLevel->update($validated);

        return back()->with('success', 'PISA level updated.');
    }

    public function destroy(PisaProficiencyLevel $pisaLevel)
    {
        $pisaLevel->delete();

        return back()->with('success', 'PISA level deleted.');
    }

    /**
     * Daniel, 2026-10-21: "Check how this assessments are done and build an
     * Activity Manager... divide in the levels you search for, and they
     * would be able to make the activities for each level." Organizes
     * authoring by PisaProficiencyLevel (the only axis now - see the
     * 2026-10-21 revision below).
     *
     * Revised the same day: "for PISA i don't need to divide in Zedville
     * levels, only the PISA levels... independent from if a student is
     * Junior, Rookie or else, they will have the same activities." PISA
     * Practice is one single global CurriculumCategory now (not one per
     * Grade Level - see the make_pisa_practice_global migration), so this
     * is a flat item list per PISA level, no Grade Level sections.
     *
     * Every write (add/edit/delete item, manage its questions) still
     * reuses the existing admin.curriculum.* routes/controller methods
     * unchanged - this page is a level-first read view + a quick-add
     * form, not a parallel authoring implementation.
     */
    public function activities(PisaProficiencyLevel $pisaLevel)
    {
        $pisaCategory = CurriculumCategory::where('is_pisa_practice', true)->first();

        $items = $pisaCategory
            ? CurriculumItem::where('category_id', $pisaCategory->id)
                ->where('pisa_proficiency_level_id', $pisaLevel->id)
                ->withCount('questions')
                ->orderBy('sort_order')
                ->get()
            : collect();

        return view('admin.pisa.activities', compact('pisaLevel', 'pisaCategory', 'items'));
    }
}
