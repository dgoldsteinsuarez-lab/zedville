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
        // the Intermediate's new prices (the City Hall's letter) and, in its third month, the
        // bank's and Zed Directo's letters - before the mailbox below counts them
        \App\Services\PriceRise::announce($user);
        \App\Services\ZedDirecto::announce($user);
        // an unexpected expense not paid in its 30 days is taken; in its month, the first visit from the 7th brings it
        \App\Services\EmergencyService::chargeDue($user);
        \App\Services\EmergencyService::arrive($user);
        // in their months, the fraud call (it rings in the layout) or the fake email (App\Services\FraudService)
        \App\Services\FraudService::arrive($user);

        $data = [
            'studentName' => $user->display_name,
            'className'   => optional($user->gradeRelation)->name ?? __('dashboard.citizen_of_zedville'),
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

            // Daniel, 2026-10-23: "why do you write overdue if it's
            // completed". Finished activities aren't to-dos: a completion
            // points at its calendar event (ActivityCompletionService), and
            // only counts for the event's current run.
            $completed = \Illuminate\Support\Facades\DB::table('activity_completions')
                ->where('student_id', $user->id)
                ->get(['activity_id', 'completed_at']);

            return CalendarEvent::whereDate('end', '>=', $today)
                ->when($user->grade, fn ($q) => $q->where('classId', $user->grade))
                ->orderBy('end')
                ->get()
                ->reject(fn ($e) => $completed->contains(fn ($c) => (int) $c->activity_id === (int) $e->id
                    && (!$e->start || Carbon::parse($c->completed_at)->gte($e->start))))
                // One line per activity, even if it was put on the calendar twice.
                ->unique('title')
                ->take(6)
                ->map(fn ($e) => [
                    'title' => \App\Support\CalendarTitle::forStudent($e->title),
                    'url'   => \App\Support\CalendarLink::forStudent($e->title, $user),
                    // The due date is the last day: `end` is stored one day
                    // later for the calendar (it treats end as exclusive).
                    // It used `start`, so a running activity showed "Overdue".
                    'date'  => $e->end ? Carbon::parse($e->end)->subDay()->toDateString() : optional($e->start)->toDateString(),
                ])
                ->values()
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

        // The same numbers as the bank (bank review, 2026-10-29): before the first
        // line the balance is 0, not the salary column; "savings" is the
        // Emergency Fund, measured against its 3-month reserve (it was measured
        // against one month's 20%, so it read 100% after the first month).
        $balance = \App\Services\BankTransferService::balance($account, \App\Services\BankTransferService::CURRENT);
        $efOpen = (bool) $account->is_open_emergency_account;
        $savings = $efOpen ? (float) $account->emergency_fund_account_amount : 0.0;

        return [
            'has_account'  => true,
            'balance'      => $balance,
            'ef_open'      => $efOpen,
            'savings'      => $savings,
            'reserve_pct'  => $efOpen ? \App\Support\EmergencyFundGoal::for($user->id, $savings)['goals'][3]['pct'] : null,
        ];
    }

    private function badges($user): array
    {
        $out = [];

        // Engagement: read from the real, spec-delivered BadgeCalculatorService/
        // student_badge_records (same source as the Badges tab in Settings),
        // instead of a separate ad-hoc recalculation - this used to be its own
        // inline calculateEngagementBadge() querying a calendar_events column
        // (activityType) that no admin screen ever actually writes to (they all
        // write `position`), so it could never see a real required activity.
        // It also used to only get a fallback table-read when BOTH this and
        // FinHero's own ad-hoc calculation produced nothing, so a real
        // Engagement badge could be hidden by an unrelated FinHero quirk.
        // Fixed both problems by just reading the real system directly
        // (bugs.md, 2026-10-20).
        //
        // The latest badge earned (Daniel, 2026-11-02: "if someone gets a Badge
        // for last month, he should be able to see it in the main part"): a
        // badge is worked out when its month ends, so the current month's record
        // has none for most of the month and used to hide last month's badge.
        // Without one from a past month, a badge already reached this month.
        try {
            $overview = app(\App\Services\BadgeOverview::class)->forStudent((int) $user->id);
            foreach (['engagement' => 'levels', 'finhero' => 'fh_levels'] as $type => $levels) {
                $badge = $overview[$type];
                [$earned, $month] = $badge['latest']
                    ? [$badge['latest']['badge'], $badge['latest']['month']]
                    : [$badge['current']['badge'], now()->startOfMonth()];
                if ($earned === 'NONE') {
                    continue;
                }
                $out[] = [
                    'type'    => $type,
                    'name'    => ($type === 'engagement' ? __('badges.engagement') : 'FinHero') . ' (' . __('badges.js.' . $levels . '.' . strtolower($earned)) . ')',
                    // the medal itself (profile.partials.badge-medals), as on the Badges tab
                    'symbol'  => '#m-' . strtolower($earned),
                    'month'   => $month->copy()->locale(app()->getLocale())->isoFormat('MMMM YYYY'),
                    'message' => __('badges.dashboard_' . $type),
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

        $energy = $energyAvg === null ? '' : __($energyAvg >= 3 ? 'dashboard.mood_high_energy' : 'dashboard.mood_low_energy');
        $comfort = $pleasantAvg === null ? '' : __($pleasantAvg >= 3 ? 'dashboard.mood_comfortable' : 'dashboard.mood_uncomfortable');

        // The mood is stored in English ("excited", "At Ease"); its name comes
        // from the Mood Meter's own translations (Daniel, 2026-10-25).
        $moodKey = 'layout.mood.' . str_replace(' ', '_', strtolower((string) $label));

        return [
            'mood'   => strtolower((string) $label),
            'label'  => \Illuminate\Support\Facades\Lang::has($moodKey) ? __($moodKey) : ucfirst((string) $label),
            'detail' => trim($energy . ($energy && $comfort ? ' · ' : '') . $comfort, ' ·'),
        ];
    }

    private function mailbox($user): array
    {
        try {
            $unread = Mailbox::where('student_id', $user->id)->where('read', 0)->count();
            $latest = Mailbox::where('student_id', $user->id)->latest('id')->first()?->subject;   // in the reader's language

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
            $out[] = ['value' => $m->format('Y-m'), 'label' => $m->copy()->locale(app()->getLocale())->isoFormat(__('transactions.month_year'))];
        }

        return $out;
    }
}
