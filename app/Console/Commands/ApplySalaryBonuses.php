<?php

namespace App\Console\Commands;

use App\Models\BankAccount;
use App\Models\SalaryBonus;
use App\Models\Transaction1;
use App\Services\BalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Applies active salary bonuses (Admin Roles & Permissions doc §2.3/§4.2)
 * into the real ledger. One idempotent daily sweep keyed off
 * `last_applied_at` - deliberately NOT modeled on ProcessScheduledTransfer,
 * which self-dispatches its own next run via a queued delay() while also
 * being independently re-polled every minute by Console/Kernel.php, a
 * double-trigger shape. Here there is exactly one trigger (this command,
 * once a day) and exactly one idempotency check (`last_applied_at`).
 *
 * Follows LedgerController's rigor: DB transaction, locked balance read,
 * append-only Transaction1 row, mandatory reason (already required at
 * grant time) carried into `meta`.
 *
 *   php artisan bonuses:apply            dry run (default) - lists what it would credit
 *   php artisan bonuses:apply --write    actually credits them
 */
class ApplySalaryBonuses extends Command
{
    protected $signature = 'bonuses:apply {--write}';
    protected $description = 'Credit active salary bonuses (one-time and recurring) into the real ledger';

    public function handle()
    {
        $write = (bool) $this->option('write');
        $today = now()->startOfDay();

        $bonuses = SalaryBonus::active()
            ->where('start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->get();

        $applied = 0;

        foreach ($bonuses as $bonus) {
            if (!$this->isDue($bonus, $today)) {
                continue;
            }

            $this->line('  bonus #' . $bonus->id . ' (student ' . $bonus->student_id . ', ' . $bonus->amount . ' zeds): ' . ($write ? 'crediting' : 'would credit'));

            if ($write) {
                DB::transaction(function () use ($bonus, $today) {
                    $balance = BalanceService::lockedBalance($bonus->student_id);
                    $newBalance = round($balance + (float) $bonus->amount, 2);

                    $bankAccountId = Transaction1::where('user_id', $bonus->student_id)->latest('id')->value('bank_account_id')
                        ?? BankAccount::where('student_id', $bonus->student_id)->value('id');

                    Transaction1::create([
                        'user_id' => $bonus->student_id,
                        'sid' => $bonus->sid,
                        'bank_account_id' => $bankAccountId,
                        'transaction_date' => now(),
                        'description' => 'Bonus',
                        'type' => 'credit',
                        'category' => 'Bonus',
                        'amount' => $bonus->amount,
                        'balance' => $newBalance,
                        'is_penalty' => 0,
                        'meta' => [
                            'salary_bonus_id' => $bonus->id,
                            'granted_by' => $bonus->granted_by,
                            'reason' => $bonus->reason,
                        ],
                    ]);

                    $bonus->update(['last_applied_at' => $today]);
                });
            }

            $applied++;
        }

        $this->comment($write
            ? "Credited {$applied} bonus(es)."
            : "Dry run - would credit {$applied} bonus(es). Add --write to apply.");

        return self::SUCCESS;
    }

    private function isDue(SalaryBonus $bonus, \Carbon\Carbon $today): bool
    {
        // One-time: only ever once.
        if (!$bonus->frequency) {
            return $bonus->last_applied_at === null;
        }

        if ($bonus->last_applied_at === null) {
            return true;
        }

        $next = $bonus->frequency === 'weekly'
            ? $bonus->last_applied_at->copy()->addWeek()
            : $bonus->last_applied_at->copy()->addMonth();

        return $today->gte($next);
    }
}
