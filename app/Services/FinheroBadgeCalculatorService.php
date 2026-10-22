<?php

namespace App\Services;

use App\Models\FinheroBadgeRecord;
use App\Services\Concerns\CalculatesBadgeAccumulation;
use Illuminate\Support\Collection;
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
        $assigned    = $this->assignedActivities($studentId, $month, $year);
        $quizPts     = $this->getQuizPoints($studentId, $month, $year);
        $libraryPts  = $this->getLibraryPoints($studentId, $month, $year, $assigned);
        $activityPts = $this->getActivityPoints($studentId, $month, $year);

        // ── Calculate totals ───────────────────────────────────────
        $totalEarned    = $quizPts + $libraryPts + $activityPts;
        $totalAvailable = $this->getTotalAvailable($assigned);
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

    /**
     * 10 for the Daily Quiz, 10 more if a Library module is assigned to
     * the student that month, plus the max points of every other FinHero
     * activity assigned to them. Only the student's own assignments count -
     * other schools' and classes' activities aren't points they could earn.
     */
    private function getTotalAvailable(Collection $assigned): int
    {
        $total = 10;

        if ($assigned->contains('type', 'library')) {
            $total += 10;
        }

        return $total + (int) $assigned->where('type', '!=', 'library')->sum('max_points');
    }

    /**
     * FinHero activities assigned to this student's class, or to the whole
     * grade it belongs to, that are open at some point during the month -
     * the same rule the EFD blackboard uses to list them. Daniel,
     * 2026-10-21: "admin is the one given the module in educational
     * finance department" - assigning a Library module there is what makes
     * it the student's module for the month.
     */
    public function assignedActivities(int $studentId, int $month, int $year): Collection
    {
        $student = DB::table('users')->where('id', $studentId)->first(['sid', 'grade']);
        if (!$student || !$student->grade) {
            return collect();
        }

        $gradeId = DB::table('classes')->where('id', $student->grade)->value('grade_id');
        $monthStart = Carbon::create($year, $month, 1)->toDateString();
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        return DB::table('finhero_activity_registry as far')
            ->leftJoin('finhero_activity_defaults as fad', function ($join) {
                $join->on('fad.activity_key', '=', 'far.activity_key')->on('fad.type', '=', 'far.type');
            })
            ->where('far.is_active', true)
            ->where('far.sid', $student->sid)
            ->where(function ($q) use ($student, $gradeId) {
                $q->where('far.cid', $student->grade);
                if ($gradeId) {
                    $q->orWhere('far.grade_id', $gradeId);
                }
            })
            ->where(fn ($q) => $q->whereNull('far.start_date')->orWhere('far.start_date', '<=', $monthEnd))
            ->where(fn ($q) => $q->whereNull('far.end_date')->orWhere('far.end_date', '>=', $monthStart))
            ->select('far.type', 'far.activity_key', DB::raw('COALESCE(far.max_points, fad.max_points, 0) as max_points'))
            ->get();
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
     * Daniel, 2026-10-21: found this still pointing at a 'daily_quiz_
     * answers' table that was never created - confirmed live, this
     * crashed every time the command ran (finhero_badge_records had 0
     * rows for any student, ever). LoginQuizController already logs a
     * correct daily-quiz answer the same way every other FinHero point is
     * logged (FinheroPointService::addPoints($id, 'quiz', 'daily_quiz',
     * 1)) - this now reads that real table instead of the one that was
     * never built.
     */
    private function getQuizPoints(int $studentId, int $month, int $year): int
    {
        $raw = DB::table('finhero_student_points')
            ->where('student_id', $studentId)
            ->where('source_type', 'quiz')
            ->where('source_key', 'daily_quiz')
            ->whereMonth('earned_at', $month)
            ->whereYear('earned_at', $year)
            ->count();

        return min($raw, 10); // Hard cap at 10
    }

    /**
     * Library points for a student this month: 1 per correct answer on the
     * first attempt at a Library module (written by FinheroStudentController::
     * submitLibrary()), capped at 10 - counted when the student has a
     * Library module assigned that month.
     */
    private function getLibraryPoints(int $studentId, int $month, int $year, Collection $assigned): int
    {
        if (!$assigned->contains('type', 'library')) return 0;

        $raw = (int) DB::table('finhero_student_points')
            ->where('student_id', $studentId)
            ->where('source_type', 'library')
            ->whereMonth('earned_at', $month)
            ->whereYear('earned_at', $year)
            ->sum('points_earned');

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
        // Library modules are counted by getLibraryPoints() instead - and
        // their keys (task_1...) would otherwise collide with the Tasks'.
        $activeActivities = DB::table('finhero_activity_registry as far')
            ->leftJoin('finhero_activity_defaults as fad', 'fad.activity_key', '=', 'far.activity_key')
            ->where('far.is_active', true)
            ->where('far.type', '!=', 'library')
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
