<?php

namespace App\Console\Commands;

use App\Models\AutopilotPeriod;
use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Month-end Autopilot. Per the Admin Roles & Permissions doc, accounts on
 * Autopilot show "Autopilot - [Month]" in their transaction history.
 *
 * WHAT THIS DOES TODAY: writes that history line (amount 0, balance unchanged)
 * for every account on Autopilot that month. It does NOT move money. Applying
 * the class-average spending/savings is a separate, still-to-be-specified step
 * (see autopilot:report for the numbers it would use).
 *
 * Safe to run twice: a month is only recorded once per account.
 *
 *   php artisan autopilot:apply              dry run (default) - lists what it would record
 *   php artisan autopilot:apply --write      records the history lines
 *   php artisan autopilot:apply --month=9 --year=2026 --write
 */
class AutopilotApply extends Command
{
    protected $signature = 'autopilot:apply {--month=} {--year=} {--write}';
    protected $description = 'Record "Autopilot - [Month]" in the history of accounts on Autopilot (no money moves)';

    public function handle()
    {
        $month = (int) ($this->option('month') ?: now()->month);
        $year = (int) ($this->option('year') ?: now()->year);
        $write = (bool) $this->option('write');
        $from = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $to = date('Y-m-t 23:59:59', strtotime($from));
        $label = 'Autopilot - ' . date('F', strtotime($from));
        $key = sprintf('%04d-%02d', $year, $month);

        // 1) Students with an active approved period overlapping the month.
        $targets = AutopilotPeriod::overlappingMonth($year, $month)->pluck('student_id')->unique()->all();

        // 2) Tutors who completed no activities themselves that month and
        // have not switched their own Autopilot off (default on).
        $tutorIds = DB::table('class_tutor')
            ->join('users', 'users.id', '=', 'class_tutor.user_id')
            ->where('users.autopilot_enabled', true)
            ->pluck('class_tutor.user_id')
            ->unique();
        foreach ($tutorIds as $tid) {
            $done = DB::table('activity_completions')->where('student_id', $tid)->whereBetween('completed_at', [$from, $to])->count();
            if ($done === 0) {
                $targets[] = $tid;
            }
        }

        $recorded = 0;
        foreach (array_unique($targets) as $userId) {
            $user = User::find($userId);
            $account = BankAccount::where('student_id', $userId)->first();
            if (!$user || !$account) {
                $this->line("  skip user {$userId}: no account");
                continue;
            }

            $already = Transaction1::where('user_id', $userId)->where('category', 'Autopilot')
                ->where('meta', 'like', '%"autopilot_month":"' . $key . '"%')->exists();
            if ($already) {
                $this->line("  {$user->nickname}: already recorded for {$key}");
                continue;
            }

            $this->line("  {$user->nickname}: " . ($write ? 'recording' : 'would record') . " \"{$label}\"");
            if ($write) {
                DB::transaction(function () use ($user, $account, $label, $key, $to) {
                    $last = Transaction1::where('user_id', $user->id)->lockForUpdate()->latest('id')->first();
                    Transaction1::create([
                        'user_id' => $user->id,
                        'sid' => $user->sid,
                        'bank_account_id' => $account->id,
                        'transaction_date' => $to,
                        'description' => $label,
                        'type' => 'credit',
                        'category' => 'Autopilot',
                        'amount' => 0,
                        'balance' => $last->balance ?? 0,
                        'is_penalty' => 0,
                        'meta' => ['autopilot_month' => $key],
                    ]);
                });
                $recorded++;
            }
        }

        $this->comment($write ? "Recorded {$recorded} history line(s). No money moved." : 'Dry run - nothing written. Add --write to record.');
        return self::SUCCESS;
    }
}
