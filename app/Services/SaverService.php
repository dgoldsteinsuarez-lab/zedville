<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\SavingsTransaction;
use App\Models\User;
use App\Support\Level;
use Carbon\Carbon;

/**
 * Saving in the Intermediate and after it (Daniel, 2026-11-04: "en intermedio,
 * los 20% de ahorro no es regla fija, él deberá ajustarlo cada mes si lo
 * necesita"): the share of the salary that goes to the Emergency Fund on
 * payday is the student's to choose, and the bank shows what they have saved
 * this month - the savings goal - which "How much have I saved?" asks about.
 */
class SaverService
{
    /** The shares a student can choose, in % of the salary. */
    public const OPTIONS = [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50];

    /** The share of the salary that goes to the Emergency Fund on payday: 20%, or the student's own from the Intermediate on. */
    public static function fundPercent(?User $user, BankAccount $account): float
    {
        $default = (float) config('zedville.emargency_fund_persentage', 20);

        return Level::saver($user) && $account->emergency_fund_percent !== null ? (float) $account->emergency_fund_percent : $default;
    }

    /**
     * What went into the Emergency Fund and the Money Market this month, less
     * what came out - interest isn't saving - and that as a share of the salary.
     *
     * @return array{amount: float, percent: float}
     */
    public static function savedThisMonth(int $userId, ?Carbon $month = null): array
    {
        $month ??= now();
        $rows = SavingsTransaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->where('category', '!=', 'Interest')
            ->get(['type', 'amount']);
        $amount = round($rows->sum(fn ($t) => $t->type === 'credit' ? (float) $t->amount : -(float) $t->amount), 2);
        $salary = MonthEndPenaltyService::salary();

        return ['amount' => $amount, 'percent' => $salary > 0 ? round($amount / $salary * 100, 1) : 0.0];
    }
}
