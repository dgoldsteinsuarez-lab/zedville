<?php
// ============================================================
// FILE: app/Http/Controllers/Student/FinheroStudentController.php
// ============================================================
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FinheroBadgeRecord;
use App\Services\FinheroBadgeCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinheroStudentController extends Controller
{
    public function __construct(private FinheroBadgeCalculatorService $calculator)
    {
    }

    /** GET /finhero-badge — renders the student FinHero badge page */
    public function index()
    {
        return view('finhero.index');
    }

    /** GET /finhero-badge/data — JSON badge data for the logged-in student */
    public function data(): JsonResponse
    {
        // TODO: confirm how to get the logged-in student ID
        $studentId = Auth::id();
        $academicYear = $this->calculator->getAcademicYear(now()->month, now()->year);

        $records = FinheroBadgeRecord::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->orderBy('year')->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month' => $r->month,
                'year' => $r->year,
                'quiz_pts' => $r->quiz_pts,
                'library_pts' => $r->library_pts,
                'activity_pts' => $r->activity_pts,
                'total_earned' => $r->total_earned,
                'total_available' => $r->total_available,
                'monthly_pct' => $r->monthly_pct,
                'monthly_badge' => $r->effective_monthly_badge,
                'monthly_badge_points' => $r->monthly_badge_points,
                'accumulated_points' => $r->accumulated_points,
                'accumulated_badge' => $r->accumulated_badge,
                'is_overridden' => $r->is_overridden,
                'badge_meta' => FinheroBadgeRecord::BADGE_META[$r->effective_monthly_badge],
            ]);

        $current = $records->last();

        return response()->json([
            'student_id' => $studentId,
            'academic_year' => $academicYear,
            'current_month' => $current,
            'accumulated_badge' => $current['accumulated_badge'] ?? 'NONE',
            'accumulated_points' => $current['accumulated_points'] ?? 0,
            'history' => $records,
            'badge_meta' => FinheroBadgeRecord::BADGE_META,
        ]);
    }

    /**
     * POST /finhero/award-points
     * Called by IT from within any activity when a student earns points.
     * Body: { activity_key: 'task_1', points: 1 }
     *
     * This is the INTEGRATION POINT for every new activity IT builds.
     * When a student completes an activity, IT calls this endpoint.
     * The badge calculator picks it up on the next monthly run.
     */
    public function awardPoints(Request $request): JsonResponse
    {
        $v = $request->validate([
            'activity_key' => [
                'required',
                'string',
                'exists:finhero_activity_registry,activity_key',
            ],
        ]);

        $studentId = Auth::id();
        $now = now();

        $academicYear = $this->calculator->getAcademicYear(
            $now->month,
            $now->year
        );

        // Get the activity from the database.
        // Never trust points sent by the browser.
        $activity = DB::table('finhero_activity_registry')
            ->where('activity_key', $v['activity_key'])
            ->where('is_active', true)
            ->first();

        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity is not active.',
            ], 422);
        }

        // The row's own max_points overrides the global default for this
        // activity_key; null falls back to it.
        $maxPoints = $activity->max_points
            ?? \App\Models\FinheroActivityDefault::where('activity_key', $v['activity_key'])->value('max_points')
            ?? 0;

        // Check whether the student has already received points
        // for this activity during the current month.
        $alreadyEarned = DB::table('finhero_student_points')
            ->where('student_id', $studentId)
            ->where('source_type', 'activity')
            ->where('source_key', $v['activity_key'])
            ->whereMonth('earned_at', $now->month)
            ->whereYear('earned_at', $now->year)
            ->sum('points_earned');

        if ($alreadyEarned >= $maxPoints) {
            return response()->json([
                'success' => false,
                'message' => 'Max points already earned for this activity.',
            ], 422);
        }

        /*
         * IMPORTANT:
         * Points are now determined by the server.
         * The browser cannot submit:
         *
         * { "points": 100 }
         *
         * to control the number of points.
         */
        $toAward = $maxPoints - $alreadyEarned;

        if ($toAward <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No points available for this activity.',
            ], 422);
        }

        DB::table('finhero_student_points')->insert([
            'student_id' => $studentId,
            'month' => $now->month,
            'year' => $now->year,
            'academic_year' => $academicYear,
            'source_type' => 'activity',
            'source_key' => $v['activity_key'],
            'points_earned' => $toAward,
            'earned_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json([
            'success' => true,
            'points_awarded' => $toAward,
            'message' => "{$toAward} FinHero point(s) awarded.",
        ]);
    }
    public function taskPage($task)
    {
        $viewPath = 'finhero.' . $task;

        if (!view()->exists($viewPath)) {
            abort(404);
        }

        // Convert task-1 => task_1
        $activityKey = str_replace('-', '_', $task);

        // Get salary from DB
        $activity = DB::table('finhero_activity_registry')
            ->where('activity_key', $activityKey)
            ->where('type', 'task')
            ->first();

        $salary = $activity->salary ?? 0;

        return view($viewPath, compact('salary', 'activityKey'));
    }
    public function savePoints(Request $request)
    {
        $request->validate([
            'source_key' => ['required', 'string'],
            'approach' => ['nullable', 'integer', 'min:0', 'max:3'],
        ]);

        $studentId = auth()->id();
        $sourceKey = $request->source_key;
        $now = now();

        // A task's questions are scored individually (source_key like
        // "task_1_question_1"), but finhero_activity_registry only ever
        // has one row per whole task ("task_1") - admins configure
        // activation/points per task, not per question. Strip the
        // "_question_N" suffix to find the task that owns this question.
        $baseActivityKey = preg_replace('/_question_\d+$/', '', $sourceKey);

        // Make sure this is an active task.
        $activity = DB::table('finhero_activity_registry')
            ->where('activity_key', $baseActivityKey)
            ->where('type', 'task')
            ->where('is_active', true)
            ->first();

        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive activity.',
            ], 422);
        }

        // ── Tiered "Approach System" path (Zedville_FinHero_Point_Values_v1.pdf §2) ──
        // The client reports which attempt TIER the student is on; the
        // server - never the client - resolves that tier to a point value,
        // same principle as awardPoints() never trusting a raw point number.
        if ($request->filled('approach')) {
            $alreadyScored = DB::table('finhero_student_points')
                ->where('student_id', $studentId)
                ->where('source_type', 'task')
                ->where('source_key', $sourceKey)
                ->whereMonth('earned_at', $now->month)
                ->whereYear('earned_at', $now->year)
                ->exists();

            if ($alreadyScored) {
                return response()->json([
                    'success' => false,
                    'message' => 'This question has already been scored this month.',
                ], 422);
            }

            $pointsToAward = \App\Services\FinheroApproachSystem::pointsForTier((int) $request->approach);

            if ($pointsToAward > 0) {
                DB::table('finhero_student_points')->insert([
                    'student_id' => $studentId,
                    'month' => $now->month,
                    'year' => $now->year,
                    'academic_year' => $this->calculator->getAcademicYear($now->month, $now->year),
                    'source_type' => 'task',
                    'source_key' => $sourceKey,
                    'points_earned' => $pointsToAward,
                    'earned_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => 'saved',
                'points_awarded' => $pointsToAward,
            ]);
        }

        // ── Legacy flat path (Tasks 2-4, not yet migrated to the Approach System) ──
        // Check whether this student has already earned points
        // for this task during the current month.
        $alreadyEarned = DB::table('finhero_student_points')
            ->where('student_id', $studentId)
            ->where('source_type', 'task')
            ->where('source_key', $sourceKey)
            ->whereMonth('earned_at', $now->month)
            ->whereYear('earned_at', $now->year)
            ->sum('points_earned');

        $maxPoints = (int) ($activity->max_points
            ?? \App\Models\FinheroActivityDefault::where('activity_key', $baseActivityKey)->value('max_points')
            ?? 0);

        if ($alreadyEarned >= $maxPoints) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum points already earned.',
            ], 422);
        }

        // Determine points on the server.
        $pointsToAward = min(
            1,
            $maxPoints - $alreadyEarned
        );

        DB::table('finhero_student_points')->insert([
            'student_id' => $studentId,
            'month' => $now->month,
            'year' => $now->year,
            'academic_year' => $this->calculator->getAcademicYear(
                $now->month,
                $now->year
            ),
            'source_type' => 'task',
            'source_key' => $sourceKey,
            'points_earned' => $pointsToAward,
            'earned_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json([
            'success' => true,
            'status' => 'saved',
            'points_awarded' => $pointsToAward,
        ]);
    }
}