<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\SavingsTransaction;
use App\Models\User;
use Carbon\Carbon;

/**
 * The single write path for the Emergency Fund and the Money Market account:
 * changes the balance in bank_accounts and writes the matching history line,
 * so the history always adds up to the balance (bank review, 2026-10-29 -
 * before, money moved in and out of these accounts without any line).
 *
 * Call inside a DB transaction with the bank account row locked
 * (BankAccount::lockForUpdate()), passing that locked instance.
 */
class SavingsLedger
{
    public const ACCOUNTS = [
        'emergency' => ['amount' => 'emergency_fund_account_amount', 'open' => 'is_open_emergency_account', 'number' => 'emergency_fund_account_number'],
        'money_market' => ['amount' => 'money_market_account_amount', 'open' => 'is_open_money_market_account', 'number' => 'money_market_account_number'],
        // Zed Directo, the Intermediate's second bank (ZedDirecto): its savings account and its 6-month deposit
        'zd_savings' => ['amount' => 'zd_savings_amount', 'open' => 'is_open_zd_savings', 'number' => 'zd_savings_number'],
        'zd_deposit' => ['amount' => 'zd_deposit_amount', 'open' => 'is_open_zd_deposit', 'number' => 'zd_deposit_number'],
    ];

    /**
     * Yearly interest, as the bank shows it (2% APY / 4% APY), paid monthly by
     * savings:credit-interest; Zed Directo's savings account 7% (monthly too)
     * and its deposit «IPC + 3 %» (8% with prices 5% up, at its end).
     */
    public const RATES = ['emergency' => 0.02, 'money_market' => 0.04, 'zd_savings' => 0.07, 'zd_deposit' => 0.08];

    /**
     * A yearly rate - for a student, their Money Market's: 4% before the
     * Intermediate, 5% in it, 6% from its third month (ZedDirecto). The deposit
     * follows inflation.
     */
    public static function rate(string $key, ?User $user = null, ?Carbon $at = null): float
    {
        if ($key === 'money_market' && $user) {
            return ZedDirecto::moneyMarketRate($user, $at);
        }
        if ($key === ZedDirecto::DEPOSIT) {
            return ZedDirecto::depositRate();
        }

        return (float) config('zedville.interest_rates.' . $key, self::RATES[$key]);
    }

    /**
     * Whether the student took money out of an account in a month: the Money
     * Market then pays no interest that month, all levels (Daniel, 2026-11-08:
     * "add in the contract that taking out the money will not give them any
     * interest"). Their own transfers and transfers to friends count.
     */
    public static function withdrewIn(int $userId, string $key, Carbon $month): bool
    {
        return SavingsTransaction::where('user_id', $userId)->where('account', $key)->where('type', 'debit')
            ->whereIn('category', ['Own Transfer', 'Fund Transfer'])
            ->whereBetween('transaction_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->exists();
    }

    /** Taking money out of the Money Market costs this share of it (withdrawalFee). */
    public const WITHDRAWAL_FEE = 0.02;

    /**
     * What taking $amount out of the Money Market costs (Daniel, 2026-11-08:
     * "habría que penalizar si saca el dinero ... porque si no, gana de los
     * meses anteriores"): 2 % of it, as banks charge for taking a long-term
     * saving out early - but never more than the interest earned so far, so the
     * money put in is never lost. That month it also earns no interest.
     */
    public static function withdrawalFee(BankAccount $account, float $amount): float
    {
        $lines = SavingsTransaction::where('bank_account_id', $account->id)->where('account', 'money_market');
        $earned = (float) (clone $lines)->where('category', 'Interest')->sum('amount') - (float) (clone $lines)->where('category', 'Fee')->sum('amount');

        return round(max(0, min($amount * self::WITHDRAWAL_FEE, $earned)), 2);
    }

    /** A month of interest on a balance (the student's rate in month $at, when given). */
    public static function monthlyInterest(float $balance, string $key, ?User $user = null, ?Carbon $at = null): float
    {
        return round(max(0, $balance) * self::rate($key, $user, $at) / 12, 2);
    }

    public static function balance(BankAccount $account, string $key): float
    {
        return round((float) $account->{self::ACCOUNTS[$key]['amount']}, 2);
    }

    public static function isOpen(BankAccount $account, string $key): bool
    {
        return (int) $account->{self::ACCOUNTS[$key]['open']} === 1;
    }

    public static function post(
        BankAccount $account,
        string $key,
        string $type,
        string $category,
        float $amount,
        string $description,
        ?array $meta = null,
        $date = null
    ): SavingsTransaction {
        $column = self::ACCOUNTS[$key]['amount'];
        $balance = round(self::balance($account, $key) + ($type === 'credit' ? $amount : -$amount), 2);
        $account->{$column} = $balance;
        $account->save();

        return SavingsTransaction::create([
            'user_id' => $account->student_id,
            'bank_account_id' => $account->id,
            'account' => $key,
            'type' => $type,
            'category' => $category,
            'amount' => round($amount, 2),
            'balance' => $balance,
            'description' => $description,
            'meta' => $meta,
            'transaction_date' => $date ?? now(),
        ]);
    }
}
