<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CurriculumAttempt;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\PisaProficiencyLevel;
use App\Services\PisaLeaderboardService;
use Illuminate\Support\Facades\Auth;

/**
 * Daniel, 2026-10-21: "in PISA, you need to explain the student what PISA
 * is and when the exam is taken... use the PISA terminologie" + "divide in
 * the levels you search for." A dedicated student PISA experience,
 * replacing the old academy.pisa shortcut (which just redirected into the
 * generic Academy category view - no explainer, no level grouping).
 *
 * Grading isn't reimplemented here - every activity links out to the
 * existing academy.item/academy.item.submit routes, which already grade
 * server-side and award the participation point. This controller only
 * organizes PISA Practice content by PisaProficiencyLevel for browsing.
 */
class PisaController extends Controller
{
    /**
     * Daniel, 2026-10-21: "for PISA i don't need to divide in Zedville
     * levels, only the PISA levels... independent from if a student is
     * Junior, Rookie or else, they will have the same activities." One
     * global PISA Practice category now (grade_level_id null), not one
     * per Zedville Grade Level - every student reaches the same one.
     */
    private function studentsPisaCategory(): ?CurriculumCategory
    {
        return CurriculumCategory::where('is_pisa_practice', true)->first();
    }

    public function index()
    {
        $levels = PisaProficiencyLevel::orderBy('sort_order')->get();
        $category = $this->studentsPisaCategory();

        $itemCounts = $category
            ? CurriculumItem::where('category_id', $category->id)
                ->where('is_active', true)
                ->whereNotNull('pisa_proficiency_level_id')
                ->selectRaw('pisa_proficiency_level_id, count(*) as c')
                ->groupBy('pisa_proficiency_level_id')
                ->pluck('c', 'pisa_proficiency_level_id')
            : collect();

        return view('education.pisa.index', compact('levels', 'itemCounts'));
    }

    public function level(PisaProficiencyLevel $pisaLevel)
    {
        $category = $this->studentsPisaCategory();
        $studentId = Auth::id();

        $items = $category
            ? CurriculumItem::where('category_id', $category->id)
                ->where('pisa_proficiency_level_id', $pisaLevel->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function (CurriculumItem $item) use ($studentId) {
                    $item->best_score = CurriculumAttempt::where('item_id', $item->id)
                        ->where('student_id', $studentId)
                        ->orderByDesc('score')
                        ->first();

                    return $item;
                })
            : collect();

        return view('education.pisa.level', compact('pisaLevel', 'items'));
    }

    /**
     * Daniel, 2026-10-21: "a Game Option, where they play alone or with
     * other students of the same grade" - confirmed via AskUserQuestion as
     * an async leaderboard (not real-time/turn-based multiplayer), so this
     * is a live ranking query, not a new table or any new scoring logic.
     *
     * Scoped to the viewer's own school only now - PISA Practice became
     * one global category later the same day ("independent from if a
     * student is Junior, Rookie or else, they will have the same
     * activities"), so every student in a school is already doing the
     * exact same PISA content regardless of Grade Level, and ranking them
     * together is correct, not a privacy concern. School-scoping itself
     * is unchanged - ranking a student against a different school's
     * children would still cut against this app's one clear existing
     * convention (petitions, tutor/admin access already scoped to "your
     * own school").
     *
     * "This month" matches the one other cross-student query in this app
     * (MoodController's City Mood tab) and the app-wide participation_logs
     * month/year reset convention.
     */
    public function leaderboard(PisaLeaderboardService $leaderboardService)
    {
        $category = $this->studentsPisaCategory();
        $sid = Auth::user()->sid;
        $now = now();

        $leaderboard = $category
            ? $leaderboardService->rankingFor($category->id, $sid, $now->month, $now->year)
            : collect();

        $myRank = $leaderboard->search(fn ($row) => $row['student']->id === Auth::id());

        return view('education.pisa.leaderboard', [
            'leaderboard' => $leaderboard,
            'myRank' => $myRank === false ? null : $myRank + 1,
        ]);
    }
}
