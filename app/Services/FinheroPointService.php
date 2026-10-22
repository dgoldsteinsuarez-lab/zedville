<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class FinheroPointService
{
    public function addPoints(
        int $studentId,
        string $sourceType,   // quiz | library | activity
        string $sourceKey,    // daily_quiz, module_id, activity_key
        int $points = 1,
        ?string $academicYear = null,
        // Daniel, 2026-10-21: the PISA leaderboard month-end bonus command
        // needs to attribute points to the month it's scoring, which isn't
        // necessarily the real calendar month the command happens to run
        // in (a manual --month= backfill for a past month, in particular).
        // Both optional and default to now()'s own month/year, so every
        // pre-existing caller (which never passed these) behaves exactly
        // as before.
        ?int $month = null,
        ?int $year = null
    ): void {
        $now = now();
        $month = $month ?? $now->month;
        $year = $year ?? $now->year;

        // Auto academic year if not passed - derived from the target
        // month/year, not necessarily $now, so a backfilled past month
        // still lands in the right academic year.
        if (!$academicYear) {
            $startMonth = config('zedville.academic_year_start_month', 4);
            $academicYear = ($month >= $startMonth)
                ? $year . '-' . ($year + 1)
                : ($year - 1) . '-' . $year;
        }
        // ✅ CHECK: already inserted today?
        $alreadyExists = DB::table('finhero_student_points')
            ->where('student_id', $studentId)
            ->where('source_type', $sourceType)
            ->where('source_key', $sourceKey)
            ->whereDate('earned_at', $now->toDateString())
            ->exists();

        if ($alreadyExists) {
            return; // ❌ stop duplicate
        }
        DB::table('finhero_student_points')->insert([
            'student_id'    => $studentId,
            'month'         => $month,
            'year'          => $year,
            'academic_year' => $academicYear,
            'source_type'   => $sourceType,
            'source_key'    => $sourceKey,
            'points_earned' => $points,
            'earned_at'     => $now,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
    }
}