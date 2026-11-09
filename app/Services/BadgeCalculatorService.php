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
    // Every Required activity done is the way in; each Optional activity then
    // raises the badge one level. Bronze used to need an Optional one too, so a
    // month with no Optional activities on the calendar (October 2026: none in
    // any class) gave nobody a badge - Esperanza did all 5 Required ones with
    // the full 15 participation points and got nothing (Daniel, 2026-11-02:
    // "should have gotten minimum the least valuable one ... should we make it
    // less rigid"). And when the class has no Optional activity that month,
    // Silver needs none (Daniel, 2026-11-02: "si no hay opcional, puede recibir
    // la de plata, porque no es culpa de ellos") and Gold takes activities done
    // on the student's own, not on the class calendar ("Oro si hace otras
    // actividades no asignadas, pero si hay opcionales, no") - see
    // optionalNeeded() and ownCounts(). Platinum always needs the calendar's.
    public const OPTIONAL_THRESHOLDS = [
        'PLATINUM' => 3,
        'GOLD'     => 2,
        'SILVER'   => 1,
        'BRONZE'   => 0,
    ];

    public const PARTICIPATION_THRESHOLDS = [
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
        $requiredAssigned  = $this->getRequiredAssigned($studentId, $month, $year);
        $requiredCompleted = $this->getRequiredCompleted($studentId, $month, $year);
        $optionalAssigned  = $this->getOptionalAssigned($studentId, $month, $year);
        $optionalCompleted = $this->getOptionalCompleted($studentId, $month, $year);
        $ownCompleted      = $this->getOwnCompleted($studentId, $month, $year);
        $participationPts  = $this->getParticipationPoints($studentId, $month, $year);

        // --- Calculate monthly badge ---
        $monthlyBadge      = $this->determineMonthlyBadge(
            $requiredAssigned,
            $requiredCompleted,
            $optionalCompleted,
            $participationPts,
            $optionalAssigned,
            $ownCompleted
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
                'optional_assigned'     => $optionalAssigned,
                'optional_completed'    => $optionalCompleted,
                'own_completed'         => $ownCompleted,
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
     * $optionalAssigned: the Optional activities on the class's calendar that
     * month (null when unknown: the usual thresholds); $ownCompleted: the
     * activities the student did that weren't on it.
     */
    public function determineMonthlyBadge(
        int $requiredAssigned,
        int $requiredCompleted,
        int $optionalCompleted,
        int $participationPts,
        ?int $optionalAssigned = null,
        int $ownCompleted = 0
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
            $optionalDone = $optionalCompleted + ($this->ownCounts($level, $optionalAssigned) ? $ownCompleted : 0);
            if (
                $optionalDone      >= $this->optionalNeeded($level, $optionalAssigned) &&
                $participationPts  >= self::PARTICIPATION_THRESHOLDS[$level]
            ) {
                return $level;
            }
        }

        return 'NONE';
    }

    /**
     * The Optional activities a level needs: none for Silver when the class
     * had no Optional activity that month - it isn't the student's fault.
     */
    public function optionalNeeded(string $level, ?int $optionalAssigned = null): int
    {
        return $level === 'SILVER' && $optionalAssigned === 0 ? 0 : self::OPTIONAL_THRESHOLDS[$level];
    }

    /**
     * Whether activities done on the student's own (not on the class
     * calendar) stand in for Optional ones: for Gold, and only in a month
     * whose calendar has no Optional activity.
     */
    public function ownCounts(string $level, ?int $optionalAssigned = null): bool
    {
        return $level === 'GOLD' && $optionalAssigned === 0;
    }

    /**
     * The next level a student can reach this month and what is still missing
     * for it (the Badges page shows it): the Required activities left, the
     * Optional activities (or, for Gold in a month without any, activities on
     * the student's own) and the participation points still needed. A level
     * needing more Optional activities than the class has is skipped. Null at
     * the top level possible, or when the class has no Required activity.
     *
     * @return array{badge: string, required: int, optional: int, own: int, points: int}|null
     */
    public function nextLevel(int $requiredAssigned, int $requiredCompleted, int $optionalCompleted, int $participationPts, ?int $optionalAssigned = null, int $ownCompleted = 0): ?array
    {
        if ($requiredAssigned === 0) {
            return null;
        }
        $current = $this->determineMonthlyBadge($requiredAssigned, $requiredCompleted, $optionalCompleted, $participationPts, $optionalAssigned, $ownCompleted);
        foreach (['BRONZE', 'SILVER', 'GOLD', 'PLATINUM'] as $level) {
            $optional = $this->optionalNeeded($level, $optionalAssigned);
            $own = $this->ownCounts($level, $optionalAssigned);
            if (!$own && $optionalAssigned !== null && $optional > max($optionalAssigned, $optionalCompleted)) {
                break;
            }
            if (StudentBadgeRecord::BADGE_POINTS[$level] > StudentBadgeRecord::BADGE_POINTS[$current]) {
                $missing = max(0, $optional - $optionalCompleted - ($own ? $ownCompleted : 0));

                return [
                    'badge' => $level,
                    'required' => max(0, $requiredAssigned - $requiredCompleted),
                    'optional' => $own ? 0 : $missing,
                    'own' => $own ? $missing : 0,
                    'points' => max(0, self::PARTICIPATION_THRESHOLDS[$level] - $participationPts),
                ];
            }
        }

        return null;
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
    private function getRequiredAssigned(int $studentId, int $month, int $year): int
    {
        // Only the student's own class's events: calendar_events are
        // written one row per class, so counting every class's required
        // events made "complete 100% of Required" impossible for anyone
        // once a second class existed.
        $classId = DB::table('users')->where('id', $studentId)->value('grade');

        if (!$classId) {
            return 0;
        }

        return DB::table('calendar_events')
            ->where('classId', $classId)
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
        // Completions are recorded against the class's calendar event by
        // ActivityCompletionService - each required event counts once.
        $completedEventIds = DB::table('activity_completions')
            ->join('calendar_events', 'activity_completions.activity_id', '=', 'calendar_events.id')
            ->where('activity_completions.student_id', $studentId)
            ->where('calendar_events.position', 'required')
            ->whereMonth('activity_completions.completed_at', $month)
            ->whereYear('activity_completions.completed_at', $year)
            ->pluck('calendar_events.id');

        // The Emergency Fund activity is "open your Emergency Fund
        // Account": once the account is open it stays done, even if it was
        // opened before this month's activity was assigned.
        $fundOpen = DB::table('bank_accounts')
            ->where('student_id', $studentId)
            ->where('is_open_emergency_account', 1)
            ->exists();

        if ($fundOpen) {
            $classId = DB::table('users')->where('id', $studentId)->value('grade');

            $completedEventIds = $completedEventIds->merge(
                DB::table('calendar_events')
                    ->where('classId', $classId)
                    ->where('title', 'Emergency Fund')
                    ->where('position', 'required')
                    ->whereMonth('start', $month)
                    ->whereYear('start', $year)
                    ->pluck('id')
            );
        }

        return $completedEventIds->unique()->count();
    }

    /**
     * Count how many Optional activities are on the student's class calendar
     * for a given month (getRequiredAssigned()'s Optional twin).
     */
    public function getOptionalAssigned(int $studentId, int $month, int $year): int
    {
        $classId = DB::table('users')->where('id', $studentId)->value('grade');

        if (!$classId) {
            return 0;
        }

        return DB::table('calendar_events')
            ->where('classId', $classId)
            ->where('position', 'optional')
            ->whereMonth('start', $month)
            ->whereYear('start', $year)
            ->count();
    }

    /**
     * Count how many Optional activities a student completed in a given month.
     * Includes BOTH calendar-assigned optional AND platform optional activities.
     */
    private function getOptionalCompleted(int $studentId, int $month, int $year): int
    {
        // Completions record the activity's own key in activity_type (the
        // EFD blackboard uses it to hide done activities), so "optional"
        // comes from the calendar event they point at, the same way
        // getRequiredCompleted() reads "required". Rows that literally say
        // 'optional' still count.
        return DB::table('activity_completions')
            ->leftJoin('calendar_events', 'activity_completions.activity_id', '=', 'calendar_events.id')
            ->where('activity_completions.student_id', $studentId)
            ->where(function ($q) {
                $q->where('calendar_events.position', 'optional')
                  ->orWhere('activity_completions.activity_type', 'optional');
            })
            ->whereMonth('activity_completions.completed_at', $month)
            ->whereYear('activity_completions.completed_at', $year)
            ->count();
    }

    /**
     * Count the activities a student did in a given month that weren't on
     * their class calendar as Required or Optional: a Money Factory activity
     * done on their own (ActivityCompletionService::completeOwn()), or one
     * finished with no calendar event for the class (activity_id 0) or with a
     * free one. Rows that literally say 'optional' already count as Optional.
     */
    private function getOwnCompleted(int $studentId, int $month, int $year): int
    {
        return DB::table('activity_completions')
            ->leftJoin('calendar_events', 'activity_completions.activity_id', '=', 'calendar_events.id')
            ->where('activity_completions.student_id', $studentId)
            ->where(fn ($q) => $q->whereNull('activity_completions.activity_type')->orWhere('activity_completions.activity_type', '!=', 'optional'))
            ->where(function ($q) {
                $q->whereNull('calendar_events.id')
                  ->orWhereNull('calendar_events.position')
                  ->orWhereNotIn('calendar_events.position', ['required', 'optional']);
            })
            ->whereMonth('activity_completions.completed_at', $month)
            ->whereYear('activity_completions.completed_at', $year)
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
