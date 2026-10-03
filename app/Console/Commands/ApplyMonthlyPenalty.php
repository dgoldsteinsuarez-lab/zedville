<?php

namespace App\Console\Commands;

use App\Http\Controllers\BankController;
use App\Models\AutopilotPeriod;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\StatementGenerator;
use Illuminate\Console\Command;

/**
 * Month-end penalty for EVERY student (not only those who log in that day).
 * Reuses BankController::banks_penalty so the rules stay in one place.
 *
 * Skips: non-students, accounts created this month, students who already got
 * this month's penalty, and students on approved Autopilot that month (it would
 * punish an approved absence).
 *
 * Only scheduled when config('zedville.auto_penalty') is true (env
 * ZEDVILLE_AUTO_PENALTY). Default is a dry run:
 *   php artisan penalty:apply            preview (writes nothing)
 *   php artisan penalty:apply --write    really apply
 */
class ApplyMonthlyPenalty extends Command
{
    protected $signature = 'penalty:apply {--write}';
    protected $description = 'Apply the monthly activity penalty to all students (dry run unless --write)';

    public function handle()
    {
        $today = now();
        if (!$today->isSameDay($today->copy()->endOfMonth())) {
            $this->warn('Not the last day of the month - nothing to do.');
            return self::SUCCESS;
        }

        $write = (bool) $this->option('write');
        $onAutopilot = AutopilotPeriod::overlappingMonth($today->year, $today->month)->pluck('student_id')->all();
        $bank = app(BankController::class);
        $gen = app(StatementGenerator::class);
        $done = 0;

        foreach (User::where('role', 4)->where('created_at', '<', $today->copy()->startOfMonth())->cursor() as $student) {
            if (in_array($student->id, $onAutopilot)) {
                $this->line("  {$student->nickname}: on Autopilot - skipped");
                continue;
            }
            $has = Transaction1::where('user_id', $student->id)->where('category', 'Penalty')
                ->whereYear('transaction_date', $today->year)->whereMonth('transaction_date', $today->month)->exists();
            if ($has) {
                $this->line("  {$student->nickname}: already penalised this month");
                continue;
            }
            $this->line("  {$student->nickname}: " . ($write ? 'checking/applying' : 'would be checked'));
            if ($write) {
                $bank->banks_penalty($gen, $student);
                $done++;
            }
        }

        $this->comment($write ? "Processed {$done} student(s)." : 'Dry run - nothing written. Add --write to apply.');
        return self::SUCCESS;
    }
}
