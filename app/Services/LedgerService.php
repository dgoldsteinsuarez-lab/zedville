<?php

namespace App\Services;

use App\Models\Transaction1;

/**
 * Single write path for transactions1, replacing the "read latest balance,
 * compute new balance, Transaction1::create(...)" pattern that was
 * duplicated across BankController/EFDController/OrderController/etc.
 * (ProjectArchitecture.md §4, planning.md Phase 2 #1).
 *
 * post() re-reads the balance under a row lock on every call rather than
 * trusting a value the caller computed earlier. Call it from inside an
 * open DB::transaction() - when posting several entries in a row inside
 * one transaction, each call correctly sees the previous call's
 * not-yet-committed insert, so a loop of sequential posts still produces
 * a correct running balance without the caller having to track one.
 */
class LedgerService
{
    /**
     * Unlocked read - fine for a pre-check before opening a transaction,
     * never as the authoritative balance right before a write.
     */
    public static function currentBalance(int $userId): float
    {
        return (float) (Transaction1::where('user_id', $userId)->latest('id')->value('balance') ?? 0);
    }

    public static function lockedBalance(int $userId): float
    {
        return BalanceService::lockedBalance($userId);
    }

    /**
     * @param array{sid?: mixed, bank_account_id?: ?int, transaction_date?: mixed, is_penalty?: bool, meta?: ?array, floor_at_zero?: bool} $extra
     *   floor_at_zero: clamp a debit's resulting balance at 0 instead of
     *   letting it go negative - matches the existing fixed-bill/penalty
     *   behavior, which silently absorbs a shortfall rather than reject it
     *   (unlike transfers/orders/donations, which reject outright).
     */
    public static function post(
        int $userId,
        string $type,
        string $category,
        float $amount,
        string $description,
        array $extra = []
    ): Transaction1 {
        $balance = self::lockedBalance($userId);
        $newBalance = $type === 'credit' ? $balance + $amount : $balance - $amount;

        if (($extra['floor_at_zero'] ?? false) && $newBalance < 0) {
            $newBalance = 0.0;
        }

        return Transaction1::create([
            'user_id' => $userId,
            'sid' => $extra['sid'] ?? null,
            'bank_account_id' => $extra['bank_account_id'] ?? null,
            'transaction_date' => $extra['transaction_date'] ?? now(),
            'description' => $description,
            'type' => $type,
            'category' => $category,
            'amount' => $amount,
            'balance' => $newBalance,
            'is_penalty' => $extra['is_penalty'] ?? false,
            'meta' => $extra['meta'] ?? null,
        ]);
    }
}
