<?php

namespace App\Console\Commands;

use App\Http\Controllers\BankController;
use App\Models\User;
use App\Services\MonthEndPenaltyService;
use App\Services\StatementGenerator;
use Illuminate\Console\Command;

/**
 * Month-end fine for EVERY student (not only those who log in that day).
 * Reuses BankController::banks_penalty so the rules stay in one place: only a
 * student who never chose the plan of the month pays it - the City Hall's
 * fine, up to 99% of the salary spent, with its letter
 * (App\Services\MonthEndPenaltyService, Daniel 2026-11-07).
 *
 * Skips: non-students, students who chose a plan and students on approved
 * Autopilot that month (it would punish an approved absence). Accounts created
 * this month too, since 2026-11-07: "el primer mes también debe haber
 * penalización".
 *
 * Only scheduled when config('zedville.auto_penalty') is true (env
 * ZEDVILLE_AUTO_PENALTY). Default is a dry run:
 *   php artisan penalty:apply            preview (writes nothing)
 *   php artisan penalty:apply --write    really apply
 */
class ApplyMonthlyPenalty extends Command
{
    protected $signature = 'penalty:apply {--write}';
    protected $description = 'Apply the month-end penalty to the students without a plan of the month (dry run unless --write)';

    public function handle()
    {
        $today = now();
        if (!$today->isSameDay($today->copy()->endOfMonth())) {
            $this->warn('Not the last day of the month - nothing to do.');
            return self::SUCCESS;
        }

        $write = (bool) $this->option('write');
        $penalty = app(MonthEndPenaltyService::class);
        $bank = app(BankController::class);
        $gen = app(StatementGenerator::class);
        $done = 0;

        foreach (User::where('role', 4)->cursor() as $student) {
            if (!$penalty->due($student)) {
                continue;
            }
            $this->line("  {$student->display_name}: no plan of the month - " . ($write ? 'charging' : 'would be charged'));
            if ($write) {
                $bank->banks_penalty($gen, $student);
                $done++;
            }
        }

        $this->comment($write ? "Charged {$done} student(s)." : 'Dry run - nothing written. Add --write to apply.');
        return self::SUCCESS;
    }
}
