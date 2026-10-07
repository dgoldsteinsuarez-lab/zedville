<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\User;
use App\Helpers\MailboxScheduler;
use App\Models\Transfer;
use App\Jobs\ProcessScheduledTransfer;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Example: Run MailboxScheduler tasks
        $schedule->call(function () {
            $users = User::all();
            foreach ($users as $user) {
                MailboxScheduler::scheduleForEvent('login', $user->id);
            }
        })->everyMinute();

        // ----- Scheduled Transfers -----
        $schedule->call(function () {
            // Process "Later" transfers (scheduled_at <= now)
            $laterTransfers = Transfer::where('type', 'later')
                ->where('scheduled_at', '<=', now())
                ->get();

            foreach ($laterTransfers as $transfer) {
                ProcessScheduledTransfer::dispatch($transfer->id);
            }

            // Process "Recurring" transfers
            $recurringTransfers = Transfer::where('type', 'recurring')
                ->where(function ($query) {
                    $query->where('start_date', '<=', now())
                        ->where('end_date', '>=', now());
                })
                ->get();

            foreach ($recurringTransfers as $transfer) {
                ProcessScheduledTransfer::dispatch($transfer->id);
            }
        })->everyMinute(); // check every minute
        // ✅ ADD THIS (Badge Calculation)
        $schedule->command('badges:calculate-monthly')
            ->lastDayOfMonth('23:59')
            ->name('badges-calculate-monthly')
            ->withoutOverlapping()
            ->runInBackground();

        // Autopilot month-end: records "Autopilot - [Month]" in the history of
        // accounts on Autopilot (no money moves).
        $schedule->command('autopilot:apply --write')
            ->lastDayOfMonth('23:55')
            ->name('autopilot-apply')
            ->withoutOverlapping();

        // Month-end penalty for every student (not only those who log in that day).
        // OFF unless ZEDVILLE_AUTO_PENALTY=true - it changes real balances.
        if (config('zedville.auto_penalty')) {
            $schedule->command('penalty:apply --write')
                ->lastDayOfMonth('23:50')
                ->name('monthly-penalty')
                ->withoutOverlapping();
        }

        // ----- Monthly Salary & Bills Backfill -----
        // Salary is credited on/after the 6th, fixed bills (rent/school/electricity/
        // water/internet) on/after the 7th of every month. Previously this only ran
        // when a student logged in during that window, so a student who didn't log in
        // never received that month's salary/bills. This runs daily so every eligible
        // student gets processed regardless of login. Both StatementGenerator::generateForUser()
        // and ensureMonthlyBills() already guard against duplicate credits/debits for
        // the same month, so running this daily (or re-running after a login-triggered
        // call) is safe and will not double-credit.
        $schedule->call(function () {
            // App timezone is UTC; business/client timezone is Europe/Berlin.
            // Evaluate "which day is it" in Berlin local time so day>=6 / day>=7
            // gates line up with the actual German calendar day.
            $now = \Carbon\Carbon::now('Europe/Berlin');

            $isLastDayOfMonth = $now->isSameDay($now->copy()->endOfMonth());

            \App\Models\User::where('role', 4)
                ->chunkById(100, function ($students) use ($now, $isLastDayOfMonth) {
                    $statementGenerator = app(\App\Services\StatementGenerator::class);
                    $bankController = app(\App\Http\Controllers\BankController::class);

                    foreach ($students as $student) {
                        $accountCreatedAt = \Carbon\Carbon::parse($student->created_at);

                        // Skip students created in current month whose first statement
                        // is generated separately via direct deposit onboarding
                        $isEligible = !(
                            $accountCreatedAt->year === $now->year &&
                            $accountCreatedAt->month === $now->month
                        );

                        if (!$isEligible) {
                            continue;
                        }

                        try {
                            // 1. Credit Salary on/after 6th (+ Emergency Fund 20% auto-transfer)
                            if ($now->day >= 6) {
                                $bankController->creditMonthlySalary($student->id);
                            }

                            // 2. Debit Fixed Bills on/after 7th
                            if ($now->day >= 7) {
                                $bankController->ensureMonthlyBills($student);
                            }

                            // 3. Month-end penalty on last day of month
                            if ($isLastDayOfMonth) {
                                $penaltyExists = \App\Models\Transaction1::where('user_id', $student->id)
                                    ->where('category', 'Penalty')
                                    ->whereYear('transaction_date', $now->year)
                                    ->whereMonth('transaction_date', $now->month)
                                    ->exists();

                                if (!$penaltyExists) {
                                    $bankController->banks_penalty($statementGenerator, $student);
                                }
                            }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('Monthly finance backfill failed for user', [
                                'user_id' => $student->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                });
        })->dailyAt('00:15')
            ->timezone('Europe/Berlin')
            ->name('monthly-finance-backfill')
            ->withoutOverlapping();

        // Petitions left pending 3+ months auto-close (Admin Roles & Permissions doc §6).
        $schedule->command('petitions:expire-stale --write')
            ->dailyAt('02:00')
            ->name('petitions-expire-stale')
            ->withoutOverlapping();

        // Alerts a Tutor when one of their students has gone quiet (doc §2.3 "Receive
        // automatic alerts for inactive students"). Reuses NotificationService - no
        // new UI, the bell/drawer already exists on every authenticated page.
        $schedule->command('students:alert-inactive')
            ->dailyAt('07:00')
            ->name('students-alert-inactive')
            ->withoutOverlapping();

        // Applies one-time and recurring salary bonuses a Tutor/Super Admin granted
        // (doc §2.3, §4.2). One idempotent daily sweep keyed off `last_applied_at` -
        // deliberately not modeled on ProcessScheduledTransfer's self-dispatch, which
        // double-fires alongside this same Kernel's own polling loop above.
        $schedule->command('bonuses:apply --write')
            ->dailyAt('00:30')
            ->name('bonuses-apply')
            ->withoutOverlapping();

        // 12-month data retention (doc §7). Anonymizes, never deletes ledger/badge
        // history - see AnonymizeInactiveStudents for exactly what's overwritten.
        $schedule->command('students:anonymize-inactive --write')
            ->monthlyOn(1, '03:00')
            ->name('students-anonymize-inactive')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

}
