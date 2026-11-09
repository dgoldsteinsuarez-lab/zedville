<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\Loan;
use App\Models\LoanInstalment;
use App\Models\User;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The personal loan, from the Intermediate (proposal «El crédito», step 11;
 * Daniel, 2026-11-08: "de acuerdo"). The real rules, small: 500 to 4,000 Ƶ over
 * 6, 12 or 24 months at an 8 % TAE, in equal instalments (the French system:
 * at first more interest, at the end more capital), charged on the 7th with
 * the bills. The bank only lends if all the student's instalments together
 * stay within 20 % of the salary; there are 14 days to change one's mind (the
 * money back, no interest), and what is left can be paid off at any time with
 * no more interest. An instalment not paid by the end of its month is late;
 * two late ones in 6 months and the bank lends nothing for 6 months (the
 * credit history). A late one goes into overdraft (step 14).
 */
class LoanService
{
    public const MIN = 500;
    public const MAX = 4000;
    public const TERMS = [6, 12, 24];
    public const TAE = 0.08;
    public const MAX_SHARE = 0.20;
    public const CANCEL_DAYS = 14;
    public const LATE_LIMIT = 2;
    public const BLOCK_MONTHS = 6;
    public const DAY = 7;

    /** The current account's lines (lang/<locale>/transactions.php 'lines'). */
    public const LINE_IN = 'Personal loan';
    public const LINE_INSTALMENT = 'Loan instalment';
    public const LINE_CANCEL = 'Personal loan returned';
    public const LINE_PAYOFF = 'Personal loan paid off';

    /** Loans are for the Intermediate and the levels after it. */
    public static function offered(?User $user): bool
    {
        return Level::saver($user);
    }

    /** The monthly rate of an 8 % TAE: 1.08^(1/12) - 1. */
    public static function monthlyRate(): float
    {
        return (1 + self::TAE) ** (1 / 12) - 1;
    }

    /** Its table: equal instalments, each [number, amount, interest, capital, remaining]; the last one closes the rest. */
    public static function table(float $amount, int $months): array
    {
        $i = self::monthlyRate();
        $instalment = round($amount * $i / (1 - (1 + $i) ** -$months), 2);
        $left = round($amount, 2);
        $rows = [];
        for ($n = 1; $n <= $months; $n++) {
            $interest = round($left * $i, 2);
            $capital = $n === $months ? $left : round($instalment - $interest, 2);
            $left = round($left - $capital, 2);
            $rows[] = ['number' => $n, 'amount' => round($capital + $interest, 2), 'interest' => $interest, 'capital' => $capital, 'remaining' => max(0.0, $left)];
        }

        return $rows;
    }

    /** What it costs: ['instalment', 'total', 'interest']. */
    public static function quote(float $amount, int $months): array
    {
        $rows = self::table($amount, $months);
        $total = round(array_sum(array_column($rows, 'amount')), 2);

        return ['instalment' => $rows[0]['amount'], 'total' => $total, 'interest' => round($total - $amount, 2)];
    }

    /** What the student's active loans take each month. */
    public static function monthlyLoad(int $userId): float
    {
        return round((float) Loan::where('user_id', $userId)->where('status', 'active')->sum('instalment'), 2);
    }

    /** The most all instalments together may take: 20 % of the salary (agreed 2026-11-08: 790 Ƶ, not 35 %). */
    public static function maxLoad(int $userId): float
    {
        return round(ConsumerSurvey::salaryFor($userId) * self::MAX_SHARE, 2);
    }

    /** The credit history: two instalments late in the last 6 months and no new loan. */
    public static function blocked(int $userId): bool
    {
        return LoanInstalment::whereIn('loan_id', Loan::where('user_id', $userId)->pluck('id'))
            ->whereIn('status', ['late', 'paid_late'])
            ->whereDate('due_on', '>=', now()->subMonthsNoOverflow(self::BLOCK_MONTHS)->toDateString())
            ->count() >= self::LATE_LIMIT;
    }

    /** Why the bank says no (a flash.* key), or null. */
    public static function refusal(?User $user, float $amount, int $months): ?string
    {
        if (!$user || !self::offered($user) || !BankAccount::where('student_id', $user->id)->exists()) {
            return 'loan_not_offered';
        }
        if ($amount < self::MIN || $amount > self::MAX) {
            return 'loan_amount';
        }
        if (!in_array($months, self::TERMS, true)) {
            return 'loan_term';
        }
        if (self::blocked($user->id)) {
            return 'loan_blocked';
        }
        if (round(self::monthlyLoad($user->id) + self::quote($amount, $months)['instalment'], 2) > self::maxLoad($user->id)) {
            return 'loan_too_much';
        }

        return null;
    }

    /** The loan, or why not: the money into the current account, its instalments from next month's 7th. */
    public static function grant(User $user, float $amount, int $months): Loan|string
    {
        $amount = round($amount, 2);
        if ($why = self::refusal($user, $amount, $months)) {
            return $why;
        }

        return DB::transaction(function () use ($user, $amount, $months) {
            $account = BankAccount::where('student_id', $user->id)->lockForUpdate()->first();
            $quote = self::quote($amount, $months);
            $loan = Loan::create([
                'user_id' => $user->id, 'sid' => $user->sid, 'amount' => $amount, 'months' => $months, 'tae' => self::TAE,
                'instalment' => $quote['instalment'], 'interest_total' => $quote['interest'], 'status' => 'active',
                'cancel_until' => today()->addDays(self::CANCEL_DAYS),
            ]);
            $first = today()->startOfMonth()->addMonthNoOverflow()->day(self::DAY);
            foreach (self::table($amount, $months) as $row) {
                LoanInstalment::create(['loan_id' => $loan->id, 'number' => $row['number'], 'due_on' => $first->copy()->addMonthsNoOverflow($row['number'] - 1),
                    'amount' => $row['amount'], 'interest' => $row['interest'], 'capital' => $row['capital'], 'remaining' => $row['remaining']]);
            }
            LedgerService::post($user->id, 'credit', 'Loan', $amount, self::LINE_IN,
                ['sid' => $user->sid, 'bank_account_id' => $account->id, 'meta' => ['loan' => $loan->id]]);

            return $loan;
        });
    }

    private static function own(User $user, Loan $loan): bool
    {
        return (int) $loan->user_id === (int) $user->id && $loan->status === 'active';
    }

    /** Within the 14 days and before any instalment: the money back, no interest. Null, or why not. */
    public static function cancel(User $user, Loan $loan): ?string
    {
        if (!self::own($user, $loan)) {
            return 'loan_not_found';
        }
        if (today()->gt($loan->cancel_until) || $loan->instalments()->whereIn('status', ['paid', 'paid_late'])->exists()) {
            return 'loan_cancel_late';
        }

        return DB::transaction(function () use ($user, $loan) {
            $account = BankAccount::where('student_id', $user->id)->lockForUpdate()->first();
            $paid = LedgerService::post($user->id, 'debit', 'Loan', (float) $loan->amount, self::LINE_CANCEL,
                ['sid' => $user->sid, 'bank_account_id' => $account->id, 'only_if_enough' => true, 'meta' => ['loan' => $loan->id]]);
            if (!$paid) {
                return 'loan_no_money';
            }
            $loan->instalments()->update(['status' => 'cancelled']);
            $loan->update(['status' => 'cancelled', 'closed_at' => now()]);

            return null;
        });
    }

    /** What paying it all off now costs: the late instalments in full and the capital still to come, no more interest. */
    public static function owed(Loan $loan): float
    {
        return round((float) $loan->instalments()->where('status', 'late')->sum('amount')
            + (float) $loan->instalments()->where('status', 'pending')->sum('capital'), 2);
    }

    /** Pays it all off now. Null, or why not. */
    public static function payOff(User $user, Loan $loan): ?string
    {
        if (!self::own($user, $loan)) {
            return 'loan_not_found';
        }

        return DB::transaction(function () use ($user, $loan) {
            $account = BankAccount::where('student_id', $user->id)->lockForUpdate()->first();
            $paid = LedgerService::post($user->id, 'debit', 'Loan', self::owed($loan), self::LINE_PAYOFF,
                ['sid' => $user->sid, 'bank_account_id' => $account->id, 'only_if_enough' => true, 'meta' => ['loan' => $loan->id]]);
            if (!$paid) {
                return 'loan_no_money';
            }
            $loan->instalments()->where('status', 'late')->update(['status' => 'paid_late', 'paid_at' => now()]);
            $loan->instalments()->where('status', 'pending')->update(['status' => 'paid_early', 'paid_at' => now()]);
            $loan->update(['status' => 'paid', 'closed_at' => now()]);

            return null;
        });
    }

    /**
     * The instalments due by today, charged from the current account when the
     * money is there (the 7th, with the bills, and every day after); one not
     * paid by the end of its month is late. A loan with all paid is done.
     */
    public static function chargeDue(User $user, ?Carbon $now = null): void
    {
        $now ??= now();
        $loans = Loan::where('user_id', $user->id)->where('status', 'active')->pluck('id');
        if ($loans->isEmpty()) {
            return;
        }
        $accountId = BankAccount::where('student_id', $user->id)->value('id');
        $due = LoanInstalment::whereIn('loan_id', $loans)->whereIn('status', ['pending', 'late'])
            ->whereDate('due_on', '<=', $now->toDateString())->orderBy('due_on')->orderBy('id')->get();
        foreach ($due as $instalment) {
            DB::transaction(function () use ($user, $instalment, $accountId, $now) {
                BankAccount::whereKey($accountId)->lockForUpdate()->first();
                $paid = LedgerService::post($user->id, 'debit', 'Loan', (float) $instalment->amount, self::LINE_INSTALMENT, [
                    'sid' => $user->sid, 'bank_account_id' => $accountId, 'only_if_enough' => true,
                    'meta' => ['loan' => $instalment->loan_id, 'instalment' => $instalment->number],
                ]);
                if ($paid) {
                    $instalment->update(['status' => $instalment->status === 'late' ? 'paid_late' : 'paid', 'paid_at' => $now]);
                } elseif ($now->gt($instalment->due_on->copy()->endOfMonth())) {
                    // not paid in its month: the bank takes it anyway, into overdraft (step 14), and it counts as late
                    Overdraft::charge($user, (float) $instalment->amount, self::LINE_INSTALMENT, ['loan' => $instalment->loan_id, 'instalment' => $instalment->number]);
                    $instalment->update(['status' => 'paid_late', 'paid_at' => $now]);
                }
            });
        }
        foreach ($loans as $id) {
            if (!LoanInstalment::where('loan_id', $id)->whereIn('status', ['pending', 'late'])->exists()) {
                Loan::whereKey($id)->update(['status' => 'paid', 'closed_at' => $now]);
            }
        }
    }
}
