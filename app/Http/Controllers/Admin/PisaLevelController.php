<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
}
