<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The PISA Practice leaderboard's ranking query, shared by the live page
 * (Student\PisaController::leaderboard(), one viewer's own cohort) and the
 * month-end bonus command (every cohort) - kept in one place so the two
 * can never drift apart on what "ranked" actually means.
 *
 * A "cohort" is the (now single, global) PISA Practice category at one
 * school - see PisaController::leaderboard()'s own docblock for why it's
 * scoped by school (the deliberate privacy choice; Grade Level no longer
 * matters here since every student already does the same PISA content).
 * `categoryId` is still a parameter rather than hardcoded, so this keeps
 * working unchanged if that ever stops being true.
 */
class PisaLeaderboardService
{
    /**
     * Ranked [['student' => User, 'total' => int], ...] for one cohort,
     * best score per item summed per student, highest first.
     */
    public function rankingFor(int $categoryId, $sid, int $month, int $year): Collection
    {
        $bestScores = DB::table('curriculum_attempts as a')
            ->join('curriculum_items as i', 'i.id', '=', 'a.item_id')
            ->join('users as u', 'u.id', '=', 'a.student_id')
            ->where('i.category_id', $categoryId)
            ->where('u.sid', $sid)
            ->whereYear('a.completed_at', $year)
            ->whereMonth('a.completed_at', $month)
            ->selectRaw('a.student_id, a.item_id, MAX(a.score) as best_score')
            ->groupBy('a.student_id', 'a.item_id')
            ->get();

        $totals = $bestScores->groupBy('student_id')
            ->map(fn ($rows) => (int) $rows->sum('best_score'))
            ->sortDesc();

        $students = User::whereIn('id', $totals->keys())->get(['id', 'nickname'])->keyBy('id');

        return $totals->map(fn ($total, $studentId) => ['student' => $students->get($studentId), 'total' => $total])
            ->filter(fn ($row) => $row['student'])
            ->values();
    }

    /**
     * Every (category_id, sid) cohort with at least one PISA attempt in
     * the given month - what the month-end command iterates, so it never
     * has to know about schools or Grade Levels directly.
     */
    public function cohortsWithActivity(int $month, int $year): Collection
    {
        return DB::table('curriculum_attempts as a')
            ->join('curriculum_items as i', 'i.id', '=', 'a.item_id')
            ->join('curriculum_categories as c', 'c.id', '=', 'i.category_id')
            ->join('users as u', 'u.id', '=', 'a.student_id')
            ->where('c.is_pisa_practice', true)
            ->whereYear('a.completed_at', $year)
            ->whereMonth('a.completed_at', $month)
            ->selectRaw('distinct i.category_id, u.sid')
            ->get();
    }
}
