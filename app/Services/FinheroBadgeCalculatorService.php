<?php

namespace App\Services;

use App\Models\FinheroBadgeRecord;
use App\Services\Concerns\CalculatesBadgeAccumulation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ZEDVILLE — FinHero Badge
 * Service: FinheroBadgeCalculatorService
 *
 * Place in: app/Services/FinheroBadgeCalculatorService.php
 *
 * CONNECTION POINTS — fill in the TODOs before going live:
 *   1. getAllActiveStudents()   — your students table/query
 *   2. getQuizPoints()         — reads from your Daily Quiz answer log
 *   3. getLibraryPoints()      — reads from your Library module answer log
 *   4. getActivityPoints()     — reads from your activity completion log
 *   5. getMonthlySettings()    — reads finhero_monthly_settings
 */
class FinheroBadgeCalculatorService
{
    use CalculatesBadgeAccumulation;

    // ─────────────────────────────────────────────────────────────
    // MAIN ENTRY POINT — called by the scheduled job
    // ─────────────────────────────────────────────────────────────

    public function calculateAllStudentsForMonth(int $month, int $year): void
    {
        $academicYear = $this->getAcademicYear($month, $year);
        $settings     = $this->getMonthlySettings($month, $year);
        $students     = $this->getAllActiveStudents();

        foreach ($students as $student) {
            $this->calculateForStudent($student->id, $month, $year, $academicYear, $settings);
        }
    }

    public function calculateForStudent(
        int $studentId,
        int $month,
        int $year,
        ?string $academicYear = null,
        ?object $settings = null
    ): FinheroBadgeRecord {
        $academicYear = $academicYear ?? $this->getAcademicYear($month, $year);
        $settings     = $settings     ?? $this->getMonthlySettings($month, $year);

        // ── Gather points ──────────────────────────────────────────
        $quizPts     = $this->getQuizPoints($studentId, $month, $year);
        $libraryPts  = $this->getLibraryPoints($studentId, $month, $year, $settings->active_library_module_id ?? null);
        $activityPts = $this->getActivityPoints($studentId, $month, $year);

        // ── Calculate totals ───────────────────────────────────────
        $totalEarned    = $quizPts + $libraryPts + $activityPts;
        $totalAvailable = $this->getTotalAvailable($month, $year, $settings);
        $monthlyPct     = $totalAvailable > 0 ? round(($totalEarned / $totalAvailable) * 100, 2) : 0;

        // ── Determine monthly badge ────────────────────────────────
        $monthlyBadge = $this->determineMonthlyBadge($monthlyPct, $settings);
        $monthlyPts   = FinheroBadgeRecord::BADGE_POINTS[$monthlyBadge];

        // ── Accumulated ────────────────────────────────────────────
        $accumulatedPts   = $this->sumAccumulatedPoints(FinheroBadgeRecord::class, $studentId, $academicYear, $month, $year, $monthlyPts);
        $accumulatedBadge = $this->determineAccumulatedBadge($accumulatedPts);

        // ── Upsert ────────────────────────────────────────────────
        return FinheroBadgeRecord::updateOrCreate(
            ['student_id' => $studentId, 'month' => $month, 'year' => $year],
            [
                'academic_year'         => $academicYear,
                'quiz_pts'              => $quizPts,
                'library_pts'           => $libraryPts,
                'activity_pts'          => $activityPts,
                'total_earned'          => $totalEarned,
                'total_available'       => $totalAvailable,
                'monthly_pct'           => $monthlyPct,
                'monthly_badge'         => $monthlyBadge,
                'monthly_badge_points'  => $monthlyPts,
                'accumulated_points'    => $accumulatedPts,
                'accumulated_badge'     => $accumulatedBadge,
            ]
        );
    }

    // ─────────────────────────────────────────────────────────────
    // BADGE DETERMINATION
    // ─────────────────────────────────────────────────────────────

    public function determineMonthlyBadge(float $pct, ?object $settings = null): string
    {
        $thLegend   = $settings->threshold_legend   ?? 90;
        $thChampion = $settings->threshold_champion ?? 70;
        $thFinhero  = $settings->threshold_finhero  ?? 50;
        $thRookie   = $settings->threshold_rookie   ?? 25;

        if ($pct >= $thLegend)   return 'LEGEND';
        if ($pct >= $thChampion) return 'CHAMPION';
        if ($pct >= $thFinhero)  return 'FINHERO';
        if ($pct >= $thRookie)   return 'ROOKIE';
        return 'NONE';
    }

    public function determineAccumulatedBadge(int $totalPoints): string
    {
        return $this->lookupAccumulatedBadge(FinheroBadgeRecord::ACCUMULATED_THRESHOLDS, $totalPoints);
    }

    // ─────────────────────────────────────────────────────────────
    // TOTAL AVAILABLE POINTS
    // ─────────────────────────────────────────────────────────────

    private function getTotalAvailable(int $month, int $year, ?object $settings): int
    {
        $total = 10; // Daily Quiz always contributes max 10

        // Library module (10 pts if one is designated)
        if (!empty($settings->active_library_module_id)) {
            $total += 10;
        }

        // Active registered activities. A row's own max_points overrides
        // the global default for that activity_key; null falls back to it.
        $activityTotal = DB::table('finhero_activity_registry as far')
            ->leftJoin('finhero_activity_defaults as fad', 'fad.activity_key', '=', 'far.activity_key')
            ->where('far.is_active', true)
            ->sum(DB::raw('COALESCE(far.max_points, fad.max_points, 0)'));

        return $total + (int) $activityTotal;
    }

    // ─────────────────────────────────────────────────────────────
    // ACCUMULATED POINTS HELPER
    // ─────────────────────────────────────────────────────────────

    private function getAccumulatedPoints(
        int $studentId,
        string $academicYear,
        int $currentMonth,
        int $currentYear,
        int $currentMonthPoints
    ): int {
        $previous = FinheroBadgeRecord::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->where('year', '<', $currentYear)
                  ->orWhere(fn($q2) => $q2->where('year', $currentYear)->where('month', '<', $currentMonth));
            })
            ->sum('monthly_badge_points');

        return (int) $previous + $currentMonthPoints;
    }

    // ─────────────────────────────────────────────────────────────
    // DATA QUERIES — fill in TODOs with your actual table names
    // ─────────────────────────────────────────────────────────────

    /**
     * Get Daily Quiz points for a student this month.
     * Rules: 1 pt per correct first attempt per login. Cap at 10.
     *
     * TODO: change 'daily_quiz_answers' to your quiz answers table.
     *       Columns assumed: student_id, is_correct, answered_at, login_session_id
     *       Each row = one quiz answer in one login session.
     *       We count rows where is_correct = true, capped at 10.
     */
    private function getQuizPoints(int $studentId, int $month, int $year): int
    {
        $raw = DB::table('daily_quiz_answers')
            ->where('student_id', $studentId)
            ->where('is_correct', true)
            ->whereMonth('answered_at', $month)
            ->whereYear('answered_at', $year)
            ->count();

        return min($raw, 10); // Hard cap at 10
    }

    /**
     * Get Library module points for a student this month.
     * Only counts the designated active module.
     * Rules: 1 pt per correct first attempt per question. Max 10.
     *
     * TODO: change 'library_question_answers' to your module answers table.
     *       Columns assumed: student_id, module_id, question_id, is_correct, answered_at
     *       Count distinct questions answered correctly for the first time.
     */
    private function getLibraryPoints(int $studentId, int $month, int $year, ?int $activeModuleId): int
    {
        if (!$activeModuleId) return 0;

        // Count distinct questions the student got correct (first attempt)
        // We assume the system only logs the first correct attempt per question
        $raw = DB::table('library_question_answers')
            ->where('student_id', $studentId)
            ->where('module_id', $activeModuleId)
            ->where('is_correct', true)
            ->whereMonth('answered_at', $month)
            ->whereYear('answered_at', $year)
            ->distinct('question_id')
            ->count('question_id');

        return min($raw, 10);
    }

    /**
     * Get activity points for a student this month.
     * Reads from finhero_student_points, both source_type = 'activity'
     * (awardPoints() - one row per whole activity, source_key already
     * equals the activity_key) and source_type = 'task' (savePoints()'s
     * Approach System path - one row per scored question, source_key like
     * "task_1_question_1", rolling up to its parent task's activity_key).
     * Points are logged by IT (activity) or Tasks 1-5's own views (task).
     *
     * Each active activity/task contributes up to its max_points.
     */
    private function getActivityPoints(int $studentId, int $month, int $year): int
    {
        // Get active activities and their max points (own value overrides
        // the global default for that activity_key; null falls back to it)
        $activeActivities = DB::table('finhero_activity_registry as far')
            ->leftJoin('finhero_activity_defaults as fad', 'fad.activity_key', '=', 'far.activity_key')
            ->where('far.is_active', true)
            ->selectRaw('far.activity_key, COALESCE(far.max_points, fad.max_points, 0) as max_points')
            ->pluck('max_points', 'activity_key');

        if ($activeActivities->isEmpty()) return 0;

        $earnedRows = DB::table('finhero_student_points')
            ->where('student_id', $studentId)
            ->whereIn('source_type', ['activity', 'task'])
            ->whereMonth('earned_at', $month)
            ->whereYear('earned_at', $year)
            ->selectRaw('source_key, SUM(points_earned) as earned')
            ->groupBy('source_key')
            ->get();

        $earnedByActivity = [];
        foreach ($earnedRows as $row) {
            $baseKey = preg_replace('/_question_\d+$/', '', $row->source_key);
            $earnedByActivity[$baseKey] = ($earnedByActivity[$baseKey] ?? 0) + (int) $row->earned;
        }

        // Sum points earned per activity, capped at that activity's max
        $total = 0;
        foreach ($activeActivities as $activityKey => $maxPts) {
            $total += min($earnedByActivity[$activityKey] ?? 0, $maxPts);
        }

        return $total;
    }

    /**
     * Get monthly settings for a given month.
     * TODO: change school_id logic to match your multi-school setup.
     */
    private function getMonthlySettings(int $month, int $year): ?object
    {
        return DB::table('finhero_monthly_settings')
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }
}
