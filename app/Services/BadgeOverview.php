<?php

namespace App\Services;

use App\Models\FinheroBadgeRecord;
use App\Models\StudentBadgeRecord;
use Carbon\Carbon;

/**
 * A student's Engagement and FinHero badges for the Badges tab and the home
 * page (Daniel, 2026-11-02: "if someone gets a Badge for last month, he should
 * be able to see it in the main part ... the kids should be proud of having a
 * badge, otherwise, the badge will almost never shown"). A badge is worked out
 * when its month ends, so the month going on has none yet: what's shown first
 * is the latest badge earned - normally last month's - then this month so far
 * and what the next badge needs, the year's badge, and the ten months.
 */
class BadgeOverview
{
    public function __construct(
        private BadgeCalculatorService $engagement,
        private FinheroBadgeCalculatorService $finhero,
    ) {
    }

    /** Both badges, with this month's records brought up to date. */
    public function forStudent(int $studentId, ?Carbon $now = null): array
    {
        $now ??= now();
        $this->engagement->calculateForStudent($studentId, $now->month, $now->year);
        $this->finhero->calculateForStudent($studentId, $now->month, $now->year);

        return [
            'engagement' => $this->build('engagement', $studentId, $now),
            'finhero' => $this->build('finhero', $studentId, $now),
        ];
    }

    private function build(string $type, int $studentId, Carbon $now): array
    {
        $model = $type === 'engagement' ? StudentBadgeRecord::class : FinheroBadgeRecord::class;
        $records = $model::where('student_id', $studentId)
            ->where('academic_year', $this->engagement->getAcademicYear($now->month, $now->year))
            ->get()
            ->keyBy(fn ($record) => sprintf('%04d-%02d', $record->year, $record->month));

        // the academic year's ten months (from config zedville.academic_year_start_month)
        $startMonth = (int) config('zedville.academic_year_start_month', 4);
        $start = Carbon::create($now->month >= $startMonth ? $now->year : $now->year - 1, $startMonth, 1);
        $thisMonth = $now->copy()->startOfMonth();
        $months = [];
        for ($i = 0; $i < 10; $i++) {
            $month = $start->copy()->addMonths($i);
            $record = $records[$month->format('Y-m')] ?? null;
            $months[] = [
                'month' => $month,
                'state' => $month->equalTo($thisMonth) ? 'current' : ($month->lt($thisMonth) ? 'past' : 'future'),
                'badge' => $record ? strtoupper((string) $record->effective_monthly_badge) : null,
            ];
        }

        // the latest badge earned in a month that is over
        $latest = collect($months)->where('state', 'past')->reverse()
            ->first(fn ($m) => $m['badge'] && $m['badge'] !== 'NONE');
        $current = $records[$now->format('Y-m')] ?? null;

        return [
            'type' => $type,
            'latest' => $latest ? $latest + ['last_month' => $latest['month']->equalTo($thisMonth->copy()->subMonth())] : null,
            'current' => $type === 'engagement' ? $this->engagementMonth($current) : $this->finheroMonth($current, $now),
            'year' => $this->year($type, $current),
            'months' => $months,
        ];
    }

    /** This month so far: what's done, the badge so far and what the next one needs. */
    private function engagementMonth(?StudentBadgeRecord $record): array
    {
        $values = [
            'required_assigned' => (int) ($record->required_assigned ?? 0),
            'required_completed' => (int) ($record->required_completed ?? 0),
            'optional_assigned' => (int) ($record->optional_assigned ?? 0),
            'optional_completed' => (int) ($record->optional_completed ?? 0),
            'own_completed' => (int) ($record->own_completed ?? 0),
            'participation_points' => (int) ($record->participation_points ?? 0),
        ];

        return $values + [
            'badge' => $record ? strtoupper((string) $record->effective_monthly_badge) : 'NONE',
            'points_max' => BadgeCalculatorService::PARTICIPATION_THRESHOLDS['PLATINUM'],
            'next' => $this->engagement->nextLevel(
                $values['required_assigned'],
                $values['required_completed'],
                $values['optional_completed'],
                $values['participation_points'],
                $values['optional_assigned'],
                $values['own_completed'],
            ),
        ];
    }

    private function finheroMonth(?FinheroBadgeRecord $record, Carbon $now): array
    {
        $pct = (float) ($record->monthly_pct ?? 0);

        return [
            'badge' => $record ? strtoupper((string) $record->effective_monthly_badge) : 'NONE',
            'pct' => $pct,
            'earned' => (int) ($record->total_earned ?? 0),
            'available' => (int) ($record->total_available ?? 0),
            'thresholds' => $this->finhero->thresholds($now->month, $now->year),
            'next' => $this->finhero->nextLevel($pct, $now->month, $now->year),
        ];
    }

    /** The year's badge, its points so far and the next one's. */
    private function year(string $type, $record): array
    {
        $thresholds = $type === 'engagement' ? StudentBadgeRecord::ACCUMULATED_THRESHOLDS : FinheroBadgeRecord::ACCUMULATED_THRESHOLDS;
        $points = (int) ($record->accumulated_points ?? 0);
        $next = collect($thresholds)->sort()->first(fn ($needed) => $points < $needed);

        return [
            'badge' => strtoupper((string) ($record->accumulated_badge ?? 'NONE')),
            'points' => $points,
            'next' => $next ? ['badge' => array_search($next, $thresholds, true), 'points' => $next] : null,
        ];
    }
}
