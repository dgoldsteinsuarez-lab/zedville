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
            'studentName' => $user->name,
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

        try {
            $engBadge = $this->calculateEngagementBadge($user->id);
            if ($engBadge && $engBadge !== 'NONE') {
                $out[] = [
                    'type'    => 'engagement',
                    'name'    => 'Engagement (' . ucfirst(strtolower($engBadge)) . ')',
                    'icon'    => '🏅',
                    'message' => 'You showed up for your city every single week.',
                ];
            }

            $finBadge = $this->calculateFinHeroBadge($user->id);
            if ($finBadge && $finBadge !== 'NONE') {
                $out[] = [
                    'type'    => 'finhero',
                    'name'    => 'FinHero (' . ucfirst(strtolower($finBadge)) . ')',
                    'icon'    => '🛡️',
                    'message' => 'You kept your needs, wants and savings in balance.',
                ];
            }
        } catch (\Throwable $e) {
            // fallback
        }

        // Table record fallback if empty
        if (empty($out)) {
            try {
                $eng = StudentBadgeRecord::where('student_id', $user->id)
                    ->orderBy('year')->orderBy('month')->get()->last();

                if ($eng && $eng->monthly_badge && strtoupper($eng->monthly_badge) !== 'NONE') {
                    $out[] = [
                        'type'    => 'engagement',
                        'name'    => 'Engagement (' . ucfirst(strtolower($eng->monthly_badge)) . ')',
                        'icon'    => '🏅',
                        'message' => 'You showed up for your city every single week.',
                    ];
                }
            } catch (\Throwable $e) {}

            try {
                $fin = FinheroBadgeRecord::where('student_id', $user->id)
                    ->orderBy('year')->orderBy('month')->get()->last();

                if ($fin && $fin->monthly_badge && strtoupper($fin->monthly_badge) !== 'NONE') {
                    $out[] = [
                        'type'    => 'finhero',
                        'name'    => 'FinHero (' . ucfirst(strtolower($fin->monthly_badge)) . ')',
                        'icon'    => '🛡️',
                        'message' => 'You kept your needs, wants and savings in balance.',
                    ];
                }
            } catch (\Throwable $e) {}
        }

        return $out;
    }

    private function calculateEngagementBadge($studentId): string
    {
        $now = now();
        $month = $now->month - 1;
        $year = $now->year;

        $assignedRequired = DB::table('calendar_events')
            ->whereMonth('start', $month + 1)
            ->whereYear('start', $year)
            ->where('activityType', 'required')
            ->count();

        $completedRequired = DB::table('participation_logs')
            ->where('student_id', $studentId)
            ->where('month', $month)
            ->where('year', $year)
            ->where('action_type', 'required')
            ->count();

        $requiredPct = $assignedRequired > 0
            ? ($completedRequired / $assignedRequired) * 100
            : 0;

        $optionalCount = DB::table('participation_logs')
            ->where('student_id', $studentId)
            ->where('month', $month)
            ->where('year', $year)
            ->where('action_type', 'optional')
            ->count();

        $participationPts = DB::table('participation_logs')
            ->where('student_id', $studentId)
            ->where('month', $month)
            ->where('year', $year)
            ->sum('points_earned');

        $participationPts = min($participationPts, 15);

        if ($requiredPct == 100 && $optionalCount >= 4 && $participationPts >= 15) {
            return 'PLATINUM';
        } elseif ($requiredPct == 100 && $optionalCount >= 3 && $participationPts >= 10) {
            return 'GOLD';
        } elseif ($requiredPct == 100 && $optionalCount >= 2 && $participationPts >= 7) {
            return 'SILVER';
        } elseif ($requiredPct == 100 && $optionalCount >= 1 && $participationPts >= 5) {
            return 'BRONZE';
        }

        return 'NONE';
    }

    private function calculateFinHeroBadge($studentId): string
    {
        $now = now();
        $month = $now->month - 1;
        $year = $now->year;

        if ($now->day < 31) {
            $month--;
            if ($month < 0) {
                $month = 11;
                $year--;
            }
        }

        $settings = DB::table('finhero_monthly_settings')
            ->where('month', $month)
            ->where('year', $year)
            ->where('badge_active', 1)
            ->first();

        if (!$settings || !$settings->badge_active) {
            return 'NONE';
        }

        $quizPts = DB::table('finhero_student_points')
            ->where('student_id', $studentId)
            ->where('month', $month)
            ->where('year', $year)
            ->where('source_type', 'quiz')
            ->sum('points_earned');
        $quizPts = min($quizPts, 10);

        $libraryPts = 0;
        if ($settings->active_library_module_id) {
            $libraryPts = DB::table('finhero_student_points')
                ->where('student_id', $studentId)
                ->where('month', $month)
                ->where('year', $year)
                ->where('source_type', 'library')
                ->where('source_key', $settings->active_library_module_id)
                ->sum('points_earned');
            $libraryPts = min($libraryPts, 10);
        }

        $activeActivities = DB::table('finhero_activity_registry')
            ->where('is_active', 1)
            ->pluck('max_points', 'activity_key');

        $activityPts = 0;
        $totalActivityMax = 0;

        foreach ($activeActivities as $key => $maxPoints) {
            $earned = DB::table('finhero_student_points')
                ->where('student_id', $studentId)
                ->where('month', $month)
                ->where('year', $year)
                ->where('source_type', 'activity')
                ->where('source_key', $key)
                ->sum('points_earned');

            $activityPts += min($earned, $maxPoints);
            $totalActivityMax += $maxPoints;
        }

        $totalEarned = $quizPts + $libraryPts + $activityPts;
        $totalAvailable = 10 + ($settings->active_library_module_id ? 10 : 0) + $totalActivityMax;

        if ($totalAvailable == 0) {
            return 'NONE';
        }

        $pct = ($totalEarned / $totalAvailable) * 100;

        if ($pct >= $settings->threshold_legend) {
            return 'LEGEND';
        } elseif ($pct >= $settings->threshold_champion) {
            return 'CHAMPION';
        } elseif ($pct >= $settings->threshold_finhero) {
            return 'FINHERO';
        } elseif ($pct >= $settings->threshold_rookie) {
            return 'ROOKIE';
        }

        return 'NONE';
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
