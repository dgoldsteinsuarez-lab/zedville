<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\BankStatement;
use App\Models\FinheroBadgeRecord;
use App\Models\MoodLog;
use App\Models\StudentBadgeRecord;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\BadgeCalculatorService;
use App\Services\FinheroBadgeCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TutorStudentController extends Controller
{
    /**
     * A Tutor's own student list, grouped by class — only classes this
     * Tutor is actually assigned to (via the class_tutor pivot), never
     * other Tutors' classes. Shows both nickname and real name, since the
     * Tutor already has a legitimate need to know who's who in their own
     * class (approving petitions, granting extensions, etc. per the Admin
     * Roles & Permissions doc) — never shown to students themselves.
     *
     * Each student also gets a one-glance summary (activities this month,
     * Engagement badge, FinHero badge, latest mood) batch-loaded for the
     * whole class at once — not per student — so a Tutor can scan the
     * table and only click into a student when something looks off.
     */
    public function index(Request $request, BadgeCalculatorService $engagementCalc, FinheroBadgeCalculatorService $finheroCalc)
    {
        $tutor = $request->user();

        abort_unless($tutor->isTutor(), 403);

        $classes = $tutor->tutoredClasses()
            ->with(['grade.level', 'tutors' => function ($q) {
                $q->select('users.id', 'users.name', 'users.nickname');
            }])
            ->get();

        foreach ($classes as $class) {
            $class->setRelation(
                'students',
                User::where('role', 4)
                    ->where('grade', $class->id)
                    ->orderBy('name')
                    ->get(['id', 'name', 'nickname', 'citizenId', 'created_at'])
            );
        }

        $studentIds = $classes->flatMap(fn ($c) => $c->students->pluck('id'))->unique()->values();

        $summaries = $this->summariesFor($studentIds, $engagementCalc, $finheroCalc);

        foreach ($classes as $class) {
            foreach ($class->students as $student) {
                $student->summary = $summaries[$student->id] ?? $this->emptySummary();
            }
        }

        // Grade level first, then class (classes with no level go last).
        $levels = $classes->sortBy('name')->groupBy(fn ($c) => $c->grade?->level?->name ?? '')
            ->sortBy(fn ($group, $name) => $name === '' ? 'zzz' : $name);

        return view('tutor.students.index', compact('levels'));
    }

    /**
     * One aggregated snapshot for a single student — Engagement badge
     * history, FinHero badge history, this month's activity completions,
     * and recent mood — so a Tutor gets the "most important information"
     * in one place instead of hunting across separate admin badge pages.
     */
    public function show(Request $request, int $studentId, BadgeCalculatorService $engagementCalc, FinheroBadgeCalculatorService $finheroCalc)
    {
        abort_unless($request->user()->canManageStudent($studentId), 403);

        $student = User::where('role', 4)->findOrFail($studentId);

        $engagementYear = $engagementCalc->getAcademicYear(now()->month, now()->year);
        $finheroYear = $finheroCalc->getAcademicYear(now()->month, now()->year);

        $engagementHistory = StudentBadgeRecord::forStudent($studentId)->forYear($engagementYear)
            ->orderBy('year')->orderBy('month')->get();

        $finheroHistory = FinheroBadgeRecord::forStudent($studentId)->forYear($finheroYear)
            ->orderBy('year')->orderBy('month')->get();

        $activitiesThisMonth = DB::table('activity_completions')
            ->where('student_id', $studentId)
            ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $moodHistory = MoodLog::where('user_id', $studentId)->latest('id')->limit(14)->get()->reverse()->values();

        $balance = Transaction1::where('user_id', $studentId)->latest('id')->value('balance') ?? 0;

        $latestStatement = BankStatement::where('user_id', $studentId)->latest('year')->latest('month')->first();
        $budget = $latestStatement->statement_data['summary'] ?? null;

        return view('tutor.students.show', compact(
            'student',
            'engagementHistory',
            'finheroHistory',
            'activitiesThisMonth',
            'moodHistory',
            'balance',
            'budget'
        ));
    }

    /**
     * Batch-load the overview summary (current month only) for a set of
     * student ids in a fixed number of queries, regardless of class size.
     */
    private function summariesFor($studentIds, BadgeCalculatorService $engagementCalc, FinheroBadgeCalculatorService $finheroCalc): array
    {
        if ($studentIds->isEmpty()) {
            return [];
        }

        $month = now()->month;
        $year = now()->year;

        $activityCounts = DB::table('activity_completions')
            ->whereIn('student_id', $studentIds)
            ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('student_id')
            ->select('student_id', DB::raw('count(*) as total'))
            ->pluck('total', 'student_id');

        $engagementBadges = StudentBadgeRecord::whereIn('student_id', $studentIds)
            ->forMonth($month, $year)
            ->get()
            ->keyBy('student_id');

        $finheroBadges = FinheroBadgeRecord::whereIn('student_id', $studentIds)
            ->forMonth($month, $year)
            ->get()
            ->keyBy('student_id');

        $latestMoods = MoodLog::whereIn('user_id', $studentIds)
            ->orderByDesc('id')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($logs) => $logs->first());

        // Latest balance per student in one query: the row with the max id
        // per user_id (MySQL-safe "latest per group" via a correlated max).
        $latestTxnIds = Transaction1::whereIn('user_id', $studentIds)
            ->selectRaw('MAX(id) as id')
            ->groupBy('user_id')
            ->pluck('id');
        $balances = Transaction1::whereIn('id', $latestTxnIds)->pluck('balance', 'user_id');

        $summaries = [];
        foreach ($studentIds as $id) {
            $engagement = $engagementBadges->get($id);
            $finhero = $finheroBadges->get($id);
            $mood = $latestMoods->get($id);

            $summaries[$id] = [
                'activities_this_month' => (int) ($activityCounts[$id] ?? 0),
                'engagement_badge' => $engagement ? $engagement->effective_monthly_badge : 'NONE',
                'engagement_meta' => StudentBadgeRecord::BADGE_META[$engagement ? $engagement->effective_monthly_badge : 'NONE'],
                'finhero_badge' => $finhero ? $finhero->effective_monthly_badge : 'NONE',
                'finhero_meta' => FinheroBadgeRecord::BADGE_META[$finhero ? $finhero->effective_monthly_badge : 'NONE'],
                'mood' => $mood->mood ?? null,
                'mood_logged_at' => $mood->created_at ?? null,
                'balance' => (float) ($balances[$id] ?? 0),
            ];
        }

        return $summaries;
    }

    private function emptySummary(): array
    {
        return [
            'activities_this_month' => 0,
            'engagement_badge' => 'NONE',
            'engagement_meta' => StudentBadgeRecord::BADGE_META['NONE'],
            'finhero_badge' => 'NONE',
            'finhero_meta' => FinheroBadgeRecord::BADGE_META['NONE'],
            'mood' => null,
            'mood_logged_at' => null,
            'balance' => 0,
        ];
    }
}
