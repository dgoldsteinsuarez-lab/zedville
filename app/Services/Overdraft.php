<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Mailbox;
use App\Models\Transaction1;
use App\Models\User;
use App\Support\Hybrid;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The overdraft, from the Intermediate (proposal step 14; Daniel, 2026-11-08:
 * "de acuerdo"): only when something that must be paid isn't - a loan
 * instalment by the end of its month, an emergency after its 30 days - the bank
 * pays it anyway and the current account goes below zero (the one exception to
 * "no minus balance", 2026-10-30). A month that ends below zero costs 15 % a
 * year on what is owed plus 8 Ƶ, as in France; the next salary covers it. The
 * first time in a month, Universal Bank writes.
 */
class Overdraft
{
    public const TAE = 0.15;
    public const FEE = 8.0;

    /** The current account's line (lang/<locale>/transactions.php 'lines'). */
    public const LINE = 'Overdraft interest and fee';

    /** What the bank pays even without the money: the account may go below zero. */
    public static function charge(User $user, float $amount, string $line, array $meta = []): void
    {
        $accountId = BankAccount::where('student_id', $user->id)->value('id');
        DB::transaction(function () use ($user, $amount, $line, $meta, $accountId) {
            BankAccount::whereKey($accountId)->lockForUpdate()->first();
            $row = LedgerService::post($user->id, 'debit', 'Loan', round($amount, 2), $line,
                ['sid' => $user->sid, 'bank_account_id' => $accountId, 'meta' => $meta + ['overdraft' => true]]);
            if ((float) $row->balance < 0) {
                self::letter($user, (float) $row->balance, $line);
            }
        });
    }

    /** The balance at the end of a month: the last line on or before it. */
    public static function balanceAt(int $userId, Carbon $end): float
    {
        return (float) Transaction1::where('user_id', $userId)->where('transaction_date', '<=', $end)
            ->orderByDesc('transaction_date')->orderByDesc('id')->value('balance');
    }

    /** From the 1st: the month before, if it ended below zero, pays 15 % a year on it and 8 Ƶ. What it cost, or null. */
    public static function chargeMonthEnd(User $user, ?Carbon $now = null): ?float
    {
        if (!Level::saver($user)) {
            return null;
        }
        $now ??= now();
        $month = $now->copy()->startOfMonth()->subMonthNoOverflow();
        $balance = self::balanceAt($user->id, $month->copy()->endOfMonth());
        $tag = $month->format('Y-m');
        if ($balance >= 0 || Transaction1::where('user_id', $user->id)->where('description', self::LINE)->where('meta->month', $tag)->exists()) {
            return null;
        }
        $cost = round(-$balance * self::TAE / 12 + self::FEE, 2);
        $accountId = BankAccount::where('student_id', $user->id)->value('id');
        DB::transaction(function () use ($user, $cost, $tag, $accountId) {
            BankAccount::whereKey($accountId)->lockForUpdate()->first();
            LedgerService::post($user->id, 'debit', 'Overdraft', $cost, self::LINE,
                ['sid' => $user->sid, 'bank_account_id' => $accountId, 'meta' => ['month' => $tag]]);
        });

        return $cost;
    }

    /** Universal Bank's letter, once a month: the account is below zero, what was paid and what it costs. */
    private static function letter(User $user, float $balance, string $line): void
    {
        if (Mailbox::where('student_id', $user->id)->where('template', 'overdraft')->where('created_at', '>=', now()->startOfMonth())->exists()) {
            return;
        }
        $data = ['balance' => $balance, 'line' => $line, 'month' => now()->format('Y-m')];
        Mailbox::create([
            'student_id' => $user->id,
            'sid' => $user->sid,
            'subject' => 'Your current account is overdrawn',
            'content' => Hybrid::without(fn () => view('mailbox_templates.overdraft', $data + ['user' => $user])->render()),
            'type' => 'primary',
            'template' => 'overdraft',
            'template_data' => $data,
            'read' => 0,
        ]);
    }
}
