<?php

namespace App\Services\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Shared by BadgeCalculatorService (Engagement) and FinheroBadgeCalculatorService
 * (FinHero) — the only parts of those two calculators that are actually
 * identical. Their monthly-badge determination logic is NOT unified here:
 * Engagement is a pass/fail required-activities gate plus an optional/
 * participation threshold matrix, while FinHero is a weighted percentage-
 * of-total-available-points threshold ladder. Forcing those into one
 * generic engine would abstract away a real domain difference, not
 * duplicated code.
 */
trait CalculatesBadgeAccumulation
{
    public function getAcademicYear(int $month, int $year): string
    {
        $academicStartMonth = config('zedville.academic_year_start_month', 4);

        if ($month >= $academicStartMonth) {
            return $year . '-' . ($year + 1);
        }

        return ($year - 1) . '-' . $year;
    }

    private function getAllActiveStudents()
    {
        return DB::table('users')
            ->where('role', 4)
            ->select('id')
            ->get();
    }

    private function sumAccumulatedPoints(
        string $recordModelClass,
        int $studentId,
        string $academicYear,
        int $currentMonth,
        int $currentYear,
        int $currentMonthPoints
    ): int {
        $previousPoints = $recordModelClass::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->where('year', '<', $currentYear)
                  ->orWhere(function ($q2) use ($currentMonth, $currentYear) {
                      $q2->where('year', $currentYear)->where('month', '<', $currentMonth);
                  });
            })
            ->sum('monthly_badge_points');

        return (int) $previousPoints + $currentMonthPoints;
    }

    private function lookupAccumulatedBadge(array $thresholds, int $totalPoints): string
    {
        foreach ($thresholds as $level => $threshold) {
            if ($totalPoints >= $threshold) {
                return $level;
            }
        }

        return 'NONE';
    }
}
