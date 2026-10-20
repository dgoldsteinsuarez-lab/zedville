<?php

namespace App\Services;

use App\Models\StudentBadgeRecord;
use App\Services\Concerns\CalculatesBadgeAccumulation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ZEDVILLE — Engagement Badge
 * Service: BadgeCalculatorService
 *
 * INSTRUCTIONS FOR IT:
 * Place this file in: app/Services/BadgeCalculatorService.php
 *
 * This service contains all badge calculation logic.
 * It is called by the scheduled job at the end of each month.
 * It can also be called manually from the admin controller.
 *
 * CONNECTION POINTS — you must fill in the TODOs below:
 *   1. Query to get all active students
 *   2. Query to get Required activities assigned to the calendar for a month
 *   3. Query to get Required activities completed by a student that month
 *   4. Query to get Optional activities completed by a student that month
 *   5. Query to get Participation points earned by a student that month
 */
class BadgeCalculatorService
{
    use CalculatesBadgeAccumulation;

    // -------------------------------------------------------------------------
    // MONTHLY BADGE THRESHOLDS
    // -------------------------------------------------------------------------
    private const OPTIONAL_THRESHOLDS = [
        'PLATINUM' => 4,
        'GOLD'     => 3,
        'SILVER'   => 2,
        'BRONZE'   => 1,
    ];

    private const PARTICIPATION_THRESHOLDS = [
        'PLATINUM' => 15,
        'GOLD'     => 10,
        'SILVER'   => 7,
        'BRONZE'   => 5,
    ];

    // -------------------------------------------------------------------------
    // MAIN ENTRY POINT — called by the scheduled job
    // -------------------------------------------------------------------------

    /**
     * Calculate badges for ALL students for a given month.
     * Called automatically at end of month by the scheduler.
     *
     * @param int $month  1–12
     * @param int $year   e.g. 2026
     */
    public function calculateAllStudentsForMonth(int $month, int $year): void
    {
        $academicYear = $this->getAcademicYear($month, $year);
        $students     = $this->getAllActiveStudents();

        foreach ($students as $student) {
            $this->calculateForStudent($student->id, $month, $year, $academicYear);
        }
    }

    /**
     * Calculate badge for a single student for a given month.
     * Can be called directly to recalculate one student (e.g. after admin action).
     */
    public function calculateForStudent(int $studentId, int $month, int $year, ?string $academicYear = null): StudentBadgeRecord
    {
        $academicYear = $academicYear ?? $this->getAcademicYear($month, $year);

        // --- Gather raw data ---
        $requiredAssigned  = $this->getRequiredAssigned($month, $year);
        $requiredCompleted = $this->getRequiredCompleted($studentId, $month, $year);
        $optionalCompleted = $this->getOptionalCompleted($studentId, $month, $year);
        $participationPts  = $this->getParticipationPoints($studentId, $month, $year);

        // --- Calculate monthly badge ---
        $monthlyBadge      = $this->determineMonthlyBadge(
            $requiredAssigned,
            $requiredCompleted,
            $optionalCompleted,
            $participationPts
        );
        $monthlyBadgePts   = StudentBadgeRecord::BADGE_POINTS[$monthlyBadge];

        // --- Calculate accumulated points for the year ---
        $accumulatedPoints = $this->sumAccumulatedPoints(StudentBadgeRecord::class, $studentId, $academicYear, $month, $year, $monthlyBadgePts);
        $accumulatedBadge  = $this->determineAccumulatedBadge($accumulatedPoints);

        // --- Upsert record (insert or update if already exists) ---
        $record = StudentBadgeRecord::updateOrCreate(
            [
                'student_id' => $studentId,
                'month'      => $month,
                'year'       => $year,
            ],
            [
                'academic_year'         => $academicYear,
                'required_assigned'     => $requiredAssigned,
                'required_completed'    => $requiredCompleted,
                'optional_completed'    => $optionalCompleted,
                'participation_points'  => $participationPts,
                'monthly_badge'         => $monthlyBadge,
                'monthly_badge_points'  => $monthlyBadgePts,
                'accumulated_points'    => $accumulatedPoints,
                'accumulated_badge'     => $accumulatedBadge,
            ]
        );

        return $record;
    }

    // -------------------------------------------------------------------------
    // BADGE DETERMINATION LOGIC
    // -------------------------------------------------------------------------

    /**
     * Determine the monthly badge level based on the three criteria.
     * All three must be met simultaneously for a given level.
     */
    public function determineMonthlyBadge(
        int $requiredAssigned,
        int $requiredCompleted,
        int $optionalCompleted,
        int $participationPts
    ): string {
        // If no Required activities were assigned, no badge can be earned
        if ($requiredAssigned === 0) {
            return 'NONE';
        }

        // Student must complete 100% of Required activities for any badge
        if ($requiredCompleted < $requiredAssigned) {
            return 'NONE';
        }

        // Check from highest to lowest
        foreach (['PLATINUM', 'GOLD', 'SILVER', 'BRONZE'] as $level) {
            if (
                $optionalCompleted >= self::OPTIONAL_THRESHOLDS[$level] &&
                $participationPts  >= self::PARTICIPATION_THRESHOLDS[$level]
            ) {
                return $level;
            }
        }

        return 'NONE';
    }

    /**
     * Determine accumulated badge from total year-to-date points.
     */
    public function determineAccumulatedBadge(int $totalPoints): string
    {
        return $this->lookupAccumulatedBadge(StudentBadgeRecord::ACCUMULATED_THRESHOLDS, $totalPoints);
    }

    // -------------------------------------------------------------------------
    // ACCUMULATED POINTS HELPER
    // -------------------------------------------------------------------------

    /**
     * Sum all monthly badge points for the academic year up to and including
     * the current month being calculated.
     */
    private function getAccumulatedPoints(
        int $studentId,
        string $academicYear,
        int $currentMonth,
        int $currentYear,
        int $currentMonthPoints
    ): int {
        // Sum points from all previous months this academic year
        $previousPoints = StudentBadgeRecord::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->where(function ($q) use ($currentMonth, $currentYear) {
                // Exclude the current month (we're about to write it)
                $q->where('year', '<', $currentYear)
                  ->orWhere(function ($q2) use ($currentMonth, $currentYear) {
                      $q2->where('year', $currentYear)->where('month', '<', $currentMonth);
                  });
            })
            ->sum('monthly_badge_points');

        return (int) $previousPoints + $currentMonthPoints;
    }

    // -------------------------------------------------------------------------
    // DATA QUERIES
    // -------------------------------------------------------------------------

    /**
     * Count how many Required activities were assigned to the calendar
     * for a given month and year.
     *
     * calendar_events.position (not activityType) is the real required/
     * optional/free marker - every admin screen that actually creates
     * these events (Mba/EmergencyFund/HighBudget/LowBudgetActivity
     * PositionController, FinheroAdminController) writes to `position`.
     * `activityType` is an unrelated integer field from the generic
     * Calendar admin UI (CalendarEventController) - querying it here was
     * a regression from an earlier pass that copied a mistake already
     * present in other, now-corrected legacy code (bugs.md, 2026-10-20).
     */
    private function getRequiredAssigned(int $month, int $year): int
    {
        return DB::table('calendar_events')
            ->where('position', 'required')
            ->whereMonth('start', $month)
            ->whereYear('start', $year)
            ->count();
    }

    /**
     * Count how many Required activities a student completed in a given month.
     */
    private function getRequiredCompleted(int $studentId, int $month, int $year): int
    {
        return DB::table('activity_completions')
            ->join('calendar_events', 'activity_completions.activity_id', '=', 'calendar_events.id')
            ->where('activity_completions.student_id', $studentId)
            ->where('calendar_events.position', 'required')
            ->whereMonth('activity_completions.completed_at', $month)
            ->whereYear('activity_completions.completed_at', $year)
            ->count();
    }

    /**
     * Count how many Optional activities a student completed in a given month.
     * Includes BOTH calendar-assigned optional AND platform optional activities.
     */
    private function getOptionalCompleted(int $studentId, int $month, int $year): int
    {
        return DB::table('activity_completions')
            ->where('student_id', $studentId)
            ->where('activity_type', 'optional')
            ->whereMonth('completed_at', $month)
            ->whereYear('completed_at', $year)
            ->count();
    }

    /**
     * Get total Participation points earned by a student in a given month.
     * Points are capped at 15 for badge calculation (100% threshold).
     */
    private function getParticipationPoints(int $studentId, int $month, int $year): int
    {
        $raw = DB::table('participation_logs')
            ->where('student_id', $studentId)
            ->where('month', $month)
            ->where('year', $year)
            ->sum('points_earned');

        return min((int) $raw, 15); // Cap at 15 (= 100%)
    }
}
