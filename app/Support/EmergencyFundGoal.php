<?php

namespace App\Support;

use App\Models\Transaction1;
use Carbon\Carbon;

/**
 * The Emergency Fund's goals (bank review, 2026-10-29): 3 and 6 months of the
 * student's essential expenses - last month's Needs, this month's while there
 * is no last month. The Education department showed "500 / 3,000" to everyone
 * and the home page measured the fund against one month's 20%. With what is
 * missing, and in how many months the monthly 20% gets there.
 */
class EmergencyFundGoal
{
    public static function for(int $userId, float $balance): array
    {
        $needs = fn (Carbon $month) => (float) Transaction1::where('user_id', $userId)
            ->where('type', 'debit')->where('category', 'Needs')
            ->whereYear('transaction_date', $month->year)->whereMonth('transaction_date', $month->month)
            ->sum('amount');
        $monthly = $needs(now()->startOfMonth()->subMonth()) ?: $needs(now()) ?: 1000.0;
        $saving = round(config('zedville.monthly_salary', 3952.40) * config('zedville.emargency_fund_persentage', 20) / 100, 2);

        $goals = [];
        foreach ([3, 6] as $months) {
            $goal = round($monthly * $months, 2);
            $missing = max(0.0, round($goal - $balance, 2));
            $goals[$months] = [
                'goal' => $goal,
                'pct' => $goal > 0 ? min(100, round($balance / $goal * 100, 1)) : 100,
                'missing' => $missing,
                'months' => (int) ceil($missing / $saving),
            ];
        }

        return ['balance' => $balance, 'monthly_saving' => $saving, 'goals' => $goals];
    }
}
