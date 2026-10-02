<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentBadgeRecord;
use App\Services\BadgeCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

/**
 * ZEDVILLE — Engagement Badge
 * Controller: BadgeAdminController
 *
 * INSTRUCTIONS FOR IT:
 * 1. Place this file in: app/Http/Controllers/Admin/BadgeAdminController.php
 * 2. Register routes — see file: 6_routes_badges.php
 * 3. Protect all routes with your admin/tutor middleware
 *
 * This controller handles:
 *   - Admin manual badge override for a student/month
 *   - Removing an override (revert to calculated badge)
 *   - Academic year reset
 *   - Data retrieval for the admin/tutor badge view
 *   - Manual recalculation trigger
 */
class BadgeAdminController extends Controller
{
    public function __construct(private BadgeCalculatorService $calculator) {}

    // =========================================================================
    // BADGE DATA — for admin/tutor views
    // =========================================================================

    /**
     * GET /admin/badges/student/{studentId}
     * Returns full badge history for a student (current year + all months).
     * Used by tutor and admin to view a student's badge breakdown.
     *
     * TODO: Add middleware to ensure only tutors/admins can access this.
     */
    public function studentView($studentId)
    {
        abort_unless(auth()->user()->canManageStudent($studentId), 403);
        return view('admin.badges.index', compact('studentId'));
    }
    public function studentBadgeHistory(int $studentId): JsonResponse
    {
        abort_unless(auth()->user()->canManageStudent($studentId), 403);
        $currentAcademicYear = $this->calculator->getAcademicYear(
            now()->month,
            now()->year
        );

        $records = StudentBadgeRecord::where('student_id', $studentId)
            ->where('academic_year', $currentAcademicYear)
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($record) {
                return [
                    'month'                => $record->month,
                    'year'                 => $record->year,
                    'required_assigned'    => $record->required_assigned,
                    'required_completed'   => $record->required_completed,
                    'optional_completed'   => $record->optional_completed,
                    'participation_points' => $record->participation_points,
                    'monthly_badge'        => $record->effective_monthly_badge, // uses override if set
                    'monthly_badge_points' => $record->monthly_badge_points,
                    'accumulated_points'   => $record->accumulated_points,
                    'accumulated_badge'    => $record->accumulated_badge,
                    'is_overridden'        => $record->is_overridden,
                    'badge_meta'           => StudentBadgeRecord::BADGE_META[$record->effective_monthly_badge],
                ];
            });

        // Current month summary (most recent record)
        $currentRecord = $records->last();

        return response()->json([
            'student_id'        => $studentId,
            'academic_year'     => $currentAcademicYear,
            'current_month'     => $currentRecord,
            'accumulated_badge' => $currentRecord['accumulated_badge'] ?? 'NONE',
            'accumulated_points'=> $currentRecord['accumulated_points'] ?? 0,
            'history'           => $records,
        ]);
    }

    /**
     * GET /admin/badges/class-summary
     * Returns badge distribution for all students for a given month.
     * Admin only — class-wide overview.
     *
     * TODO: Filter by class/city ID once user object fields are confirmed.
     */
    public function classSummary(Request $request): JsonResponse
    {
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);

        $summary = StudentBadgeRecord::when(auth()->user()->manageableStudentIds() !== null, fn ($q) => $q->whereIn('student_id', auth()->user()->manageableStudentIds()))
            ->where('month', $month)
            ->where('year', $year)
            ->selectRaw('monthly_badge, COUNT(*) as count')
            ->groupBy('monthly_badge')
            ->pluck('count', 'monthly_badge');

        return response()->json([
            'month'   => $month,
            'year'    => $year,
            'summary' => [
                'PLATINUM' => $summary['PLATINUM'] ?? 0,
                'GOLD'     => $summary['GOLD']     ?? 0,
                'SILVER'   => $summary['SILVER']   ?? 0,
                'BRONZE'   => $summary['BRONZE']   ?? 0,
                'NONE'     => $summary['NONE']     ?? 0,
            ],
        ]);
    }

    // =========================================================================
    // ADMIN OVERRIDE
    // =========================================================================

    /**
     * POST /admin/badges/override
     * Manually set a student's monthly badge for a specific month.
     * The accumulated badge is automatically recalculated after the override.
     *
     * Request body:
     * {
     *   "student_id": 42,
     *   "month": 3,
     *   "year": 2026,
     *   "badge": "GOLD"    // PLATINUM | GOLD | SILVER | BRONZE | NONE
     * }
     */
    public function override(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
            'month'      => 'required|integer|min:1|max:12',
            'year'       => 'required|integer|min:2020|max:2100',
            'badge'      => 'required|in:PLATINUM,GOLD,SILVER,BRONZE,NONE',
        ]);

        if (!empty($validated['student_id'])) { abort_unless(auth()->user()->canManageStudent($validated['student_id']), 403); }

        $record = StudentBadgeRecord::where('student_id', $validated['student_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->first();

        if (!$record) {
            return response()->json(['error' => 'No badge record found for this student and month.'], 404);
        }

        // Apply override
        $record->update([
            'is_overridden'  => true,
            'override_badge' => $validated['badge'],
            'overridden_at'  => now(),
        ]);

        // Recalculate accumulated badge for the rest of the year
        // since the effective monthly points may have changed
        $this->recalculateAccumulatedFromMonth(
            $validated['student_id'],
            $validated['month'],
            $validated['year'],
            $record->academic_year
        );

        return response()->json([
            'success' => true,
            'message' => "Badge overridden to {$validated['badge']} for student {$validated['student_id']}.",
        ]);
    }

    /**
     * POST /admin/badges/override/remove
     * Remove an admin override and revert to the system-calculated badge.
     *
     * Request body:
     * { "student_id": 42, "month": 3, "year": 2026 }
     */
    public function removeOverride(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
            'month'      => 'required|integer|min:1|max:12',
            'year'       => 'required|integer|min:2020|max:2100',
        ]);

        if (!empty($validated['student_id'])) { abort_unless(auth()->user()->canManageStudent($validated['student_id']), 403); }

        $record = StudentBadgeRecord::where('student_id', $validated['student_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->first();

        if (!$record) {
            return response()->json(['error' => 'Record not found.'], 404);
        }

        $record->update([
            'is_overridden'  => false,
            'override_badge' => null,
            'overridden_at'  => null,
        ]);

        // Recalculate accumulated after removing override
        $this->recalculateAccumulatedFromMonth(
            $validated['student_id'],
            $validated['month'],
            $validated['year'],
            $record->academic_year
        );

        return response()->json(['success' => true, 'message' => 'Override removed. Reverted to calculated badge.']);
    }

    // =========================================================================
    // ACADEMIC YEAR RESET
    // =========================================================================

    /**
     * POST /admin/badges/reset-year
     * Manually trigger an academic year reset.
     * This closes the current year and starts fresh accumulation.
     *
     * Historical records are KEPT in the database — only accumulated_points
     * and accumulated_badge will reset for the new year's records.
     * Old records remain for historical reporting.
     *
     * Request body:
     * { "new_academic_year": "2026-2027" }
     */
    public function resetAcademicYear(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'new_academic_year' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
        ]);

        // No data is deleted — the new year simply starts with no records.
        // The next time the monthly job runs, it will create records
        // under the new academic_year string, starting accumulation from 0.

        // Optionally, you may want to store a "year_closed" flag somewhere.
        // For now, the academic year is determined automatically from the
        // month/year in BadgeCalculatorService::getAcademicYear().

        return response()->json([
            'success'          => true,
            'message'          => "Academic year reset. New year: {$validated['new_academic_year']}. Accumulated points will restart from 0 for new monthly records.",
            'new_academic_year'=> $validated['new_academic_year'],
        ]);
    }

    // =========================================================================
    // MANUAL RECALCULATION
    // =========================================================================

    /**
     * POST /admin/badges/recalculate
     * Manually trigger badge recalculation for a student or all students.
     * Useful for testing or if the scheduled job failed.
     *
     * Request body:
     * { "month": 3, "year": 2026, "student_id": 42 }  // student_id optional
     */
    public function recalculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month'      => 'required|integer|min:1|max:12',
            'year'       => 'required|integer|min:2020|max:2100',
            'student_id' => 'nullable|integer|exists:users,id',
        ]);

        if (!empty($validated['student_id'])) { abort_unless(auth()->user()->canManageStudent($validated['student_id']), 403); }

        $month = $validated['month'];
        $year  = $validated['year'];

        if (!empty($validated['student_id'])) {
            $academicYear = $this->calculator->getAcademicYear($month, $year);
            $this->calculator->calculateForStudent($validated['student_id'], $month, $year, $academicYear);
            $message = "Recalculated badge for student {$validated['student_id']} for {$month}/{$year}.";
        } else {
            $this->calculator->calculateAllStudentsForMonth($month, $year);
            $message = "Recalculated badges for all students for {$month}/{$year}.";
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * After an override, recalculate accumulated_points for all subsequent
     * months in the same academic year so the running total stays correct.
     */
    private function recalculateAccumulatedFromMonth(
        int $studentId,
        int $fromMonth,
        int $fromYear,
        string $academicYear
    ): void {
        // Get all records for this student in this academic year
        // from the affected month onward, in chronological order
        $records = StudentBadgeRecord::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->where(function ($q) use ($fromMonth, $fromYear) {
                $q->where('year', '>', $fromYear)
                  ->orWhere(function ($q2) use ($fromMonth, $fromYear) {
                      $q2->where('year', $fromYear)->where('month', '>=', $fromMonth);
                  });
            })
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Recalculate accumulated points running total from scratch
        $runningTotal = StudentBadgeRecord::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->where(function ($q) use ($fromMonth, $fromYear) {
                $q->where('year', '<', $fromYear)
                  ->orWhere(function ($q2) use ($fromMonth, $fromYear) {
                      $q2->where('year', $fromYear)->where('month', '<', $fromMonth);
                  });
            })
            ->sum('monthly_badge_points');

        foreach ($records as $record) {
            // Use override badge points if overridden
            $effectiveBadge = $record->is_overridden && $record->override_badge
                ? $record->override_badge
                : $record->monthly_badge;

            $pts = StudentBadgeRecord::BADGE_POINTS[$effectiveBadge];
            $runningTotal += $pts;

            $record->update([
                'accumulated_points' => $runningTotal,
                'accumulated_badge'  => $this->calculator->determineAccumulatedBadge($runningTotal),
            ]);
        }
    }
}
