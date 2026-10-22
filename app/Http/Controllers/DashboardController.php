<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CalendarEvent;
use App\Models\FinheroBadgeRecord;
use App\Models\Mailbox;
use App\Models\MoodLog;
use App\Models\StudentBadgeRecord;
use App\Models\Transaction1;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ZEDVILLE — Student home / dashboard.
 *
 * Server-rendered summary shown when a citizen clicks "Home" in the
 * left sidebar. Every widget degrades gracefully (empty state) when the
 * underlying data is missing.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $data = [
            'studentName' => $user->nickname,
            'className'   => optional($user->gradeRelation)->name ?? 'Citizen of Zedville',
            'activities'  => $this->activities($user),
            'bank'        => $this->bankSummary($user),
            'badges'      => $this->badges($user),
            'mood'        => $this->moodSummary($user),
            'mailbox'     => $this->mailbox($user),
            'months'      => $this->recentMonths(),
        ];

        return view('dashboard.home', $data);
    }

    /* ---------------------------------------------------------------- */

    private function activities($user): array
    {
        try {
            $today = Carbon::today();

            return CalendarEvent::whereDate('end', '>=', $today)
                ->when($user->grade, fn ($q) => $q->where('classId', $user->grade))
                ->orderBy('start')
                ->limit(6)
                ->get()
                ->map(fn ($e) => [
                    'title' => $e->title,
                    'date'  => optional($e->start)->toDateString(),
                ])
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function bankSummary($user): array
    {
        $account = BankAccount::where('student_id', $user->id)->first();

        if (! $account) {
            return ['has_account' => false];
        }

        $latestTxn = Transaction1::where('user_id', $user->id)->latest('id')->first();

        $balance = $latestTxn
            ? (float) $latestTxn->balance
            : (float) ($account->primary_savings_account_amount ?? 0);

        $savings = (float) ($account->emergency_fund_account_amount ?? 0);

        $salaryamount = config('zedville.monthly_salary', 3952.40);
        $emmsavingsPct = config('zedville.emargency_fund_persentage', 20);
        $savings_goal = round($salaryamount * ($emmsavingsPct / 100), 2);

        return [
            'has_account'  => true,
            'balance'      => $balance,
            'savings'      => $savings,
            'savings_goal' => $savings_goal,
        ];
    }

    private function badges($user): array
    {
        $out = [];

        // Engagement: read from the real, spec-delivered BadgeCalculatorService/
        // student_badge_records (same source as the Settings page and /badges),
        // instead of a separate ad-hoc recalculation - this used to be its own
        // inline calculateEngagementBadge() querying a calendar_events column
        // (activityType) that no admin screen ever actually writes to (they all
        // write `position`), so it could never see a real required activity.
        // It also used to only get a fallback table-read when BOTH this and
        // FinHero's own ad-hoc calculation produced nothing, so a real
        // Engagement badge could be hidden by an unrelated FinHero quirk.
        // Fixed both problems by just reading the real system directly
        // (bugs.md, 2026-10-20).
        try {
            app(\App\Services\BadgeCalculatorService::class)
                ->calculateForStudent($user->id, now()->month, now()->year);

            $eng = StudentBadgeRecord::where('student_id', $user->id)
                ->orderBy('year')->orderBy('month')->get()->last();

            if ($eng && $eng->effective_monthly_badge && strtoupper($eng->effective_monthly_badge) !== 'NONE') {
                $out[] = [
                    'type'    => 'engagement',
                    'name'    => 'Engagement (' . ucfirst(strtolower($eng->effective_monthly_badge)) . ')',
                    'icon'    => '🏅',
                    'message' => 'You showed up for your city every single week.',
                ];
            }
        } catch (\Throwable $e) {}

        // FinHero: the latest record from FinheroBadgeCalculatorService (the
        // same source as the profile page), which counts Daily Quiz,
        // Library and Task points.
        try {
            $fin = FinheroBadgeRecord::where('student_id', $user->id)
                ->orderBy('year')->orderBy('month')->get()->last();
            $finBadge = $fin ? strtoupper((string) $fin->effective_monthly_badge) : 'NONE';

            if ($finBadge !== '' && $finBadge !== 'NONE') {
                $out[] = [
                    'type'    => 'finhero',
                    'name'    => 'FinHero (' . ucfirst(strtolower($finBadge)) . ')',
                    'icon'    => '🛡️',
                    'message' => 'You kept your needs, wants and savings in balance.',
                ];
            }
        } catch (\Throwable $e) {}

        return $out;
    }

    private function moodSummary($user): ?array
    {
        try {
            $start = Carbon::now()->startOfMonth();

            $mine = MoodLog::where('user_id', $user->id)
                ->where('created_at', '>=', $start)
                ->get();

            if ($mine->isEmpty()) {
                return null;
            }

            $cityQuery = MoodLog::join('users', 'users.id', '=', 'mood_logs.user_id')
                ->where('mood_logs.created_at', '>=', $start);

            if ($user->sid) {
                $cityQuery->where('users.sid', $user->sid);
            }
            if ($user->grade) {
                $cityQuery->where('users.grade', $user->grade);
            }

            $city = $cityQuery->select('mood_logs.*')->get();

            return [
                'my_mood'   => $this->moodLabel($mine),
                'city_mood' => $this->moodLabel($city->isNotEmpty() ? $city : $mine),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function moodLabel($logs): array
    {
        $label = $logs->groupBy('mood')->sortByDesc->count()->keys()->first() ?: '—';

        $energyAvg = $logs->avg('energy');
        $pleasantAvg = $logs->avg('pleasantness');

        $energy = $energyAvg === null ? '' : ($energyAvg >= 3 ? 'High energy' : 'Low energy');
        $comfort = $pleasantAvg === null ? '' : ($pleasantAvg >= 3 ? 'comfortable' : 'uncomfortable');

        return [
            'mood'   => strtolower((string) $label),
            'label'  => ucfirst((string) $label),
            'detail' => trim($energy . ($energy && $comfort ? ' · ' : '') . $comfort, ' ·'),
        ];
    }

    private function mailbox($user): array
    {
        try {
            $unread = Mailbox::where('student_id', $user->id)->where('read', 0)->count();
            $latest = Mailbox::where('student_id', $user->id)->latest('id')->value('subject');

            return ['unread' => $unread, 'latest' => $latest];
        } catch (\Throwable $e) {
            return ['unread' => 0, 'latest' => null];
        }
    }

    private function recentMonths(): array
    {
        $out = [];
        $now = Carbon::now()->startOfMonth();

        for ($i = 0; $i < 6; $i++) {
            $m = (clone $now)->subMonths($i);
            $out[] = ['value' => $m->format('Y-m'), 'label' => $m->format('F Y')];
        }

        return $out;
    }
}
