<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\User;
use App\Helpers\MailboxScheduler;

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

        // ----- Scheduled transfers and bill payments -----
        // The ones that are due, each date once (bank review, 2026-10-29: the
        // old job here ran every past "later" transfer again every minute).
        $schedule->command('transfers:run-due')
            ->everyMinute()
            ->name('transfers-run-due')
            ->withoutOverlapping();

        // Interest on the Emergency Fund and Money Market accounts, for the month
        // that just ended - the bank promises it "on the 1st of each month".
        $schedule->command('savings:credit-interest --write')
            ->monthlyOn(1, '01:00')
            ->timezone('Europe/Berlin')
            ->name('savings-credit-interest')
            ->withoutOverlapping();
        // ✅ ADD THIS (Badge Calculation)
        $schedule->command('badges:calculate-monthly')
            ->lastDayOfMonth('23:59')
            ->name('badges-calculate-monthly')
            ->withoutOverlapping()
            ->runInBackground();

        // FinHero badge calculation - this command has existed since it
        // was written (its own file header even says to register it here)
        // but was never actually scheduled, confirmed live: finhero_badge_
        // records has 0 rows for any student, ever, while the Engagement
        // badge job above has been producing real rows every month. Same
        // slot as the Engagement badge job it mirrors (bugs.md, 2026-10-21).
        $schedule->command('finhero:calculate-monthly')
            ->lastDayOfMonth('23:59')
            ->name('finhero-calculate-monthly')
            ->withoutOverlapping()
            ->runInBackground();

        // PISA leaderboard month-end bonus (bugs.md, 2026-10-21) - same
        // month-end slot as the badge calculation above, not a separate
        // cron entry for "things that happen when the month ends."
        $schedule->command('pisa:award-leaderboard-bonus')
            ->lastDayOfMonth('23:59')
            ->name('pisa-award-leaderboard-bonus')
            ->withoutOverlapping()
            ->runInBackground();

        // The plan of the month's payments on their Saturdays (2026-10-31), and
        // those waiting for money (2026-11-02).
        $schedule->command('plans:charge')
            ->dailyAt('07:00')
            ->name('plans-charge')
            ->withoutOverlapping();

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

        // The fine for each Required activity not done in time, the day after its
        // deadline (Daniel, 2026-11-03). Its amount - 0 turns it off - is set in
        // Admin > Bank > Activity Fines.
        $schedule->command('fines:apply --write')
            ->dailyAt('00:20')
            ->name('activity-fines')
            ->withoutOverlapping();

        // Zed Directo's 6-month deposits that end today: their money and interest
        // back to the savings account (App\Services\ZedDirecto)
        $schedule->call(fn () => \App\Services\ZedDirecto::endDue())
            ->dailyAt('00:25')
            ->name('zed-directo-deposits')
            ->withoutOverlapping();

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

            \App\Models\User::where('role', 4)
                ->chunkById(100, function ($students) use ($now) {
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
                            // 0. The City Hall's letter about the Intermediate's new prices, and in its
                            // third month the bank's and Zed Directo's letters, from the 1st
                            \App\Services\PriceRise::announce($student);
                            \App\Services\ZedDirecto::announce($student);
                            // loan instalments (late ones into overdraft) and, from the 1st, a month that ended below zero
                            \App\Services\LoanService::chargeDue($student);
                            // the credit card's statement (from the 7th; a late one into overdraft)
                            \App\Services\CreditCardService::settle($student);
                            \App\Services\Overdraft::chargeMonthEnd($student);

                            // 1. Credit Salary on/after 6th (+ Emergency Fund 20% auto-transfer)
                            if ($now->day >= 6) {
                                $bankController->creditMonthlySalary($student->id);
                            }

                            // 2. Debit Fixed Bills on/after 7th
                            if ($now->day >= 7) {
                                $bankController->ensureMonthlyBills($student);
                            }
                            // 3. An unexpected expense not paid in its 30 days, taken (into overdraft if
                            // need be); and on the month's last day, its emergency for whoever didn't come in
                            \App\Services\EmergencyService::chargeDue($student);
                            \App\Services\EmergencyService::arrive($student, true);
                            // (The month-end penalty is not here: it ran for everyone at
                            // 00:15 on the last day, before that day's spending and even
                            // with the penalty switched off. penalty:apply (when
                            // ZEDVILLE_AUTO_PENALTY is on) and the last-day login do it.)
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

        // Sends any email_campaigns row whose fixed_date/relative-to-school-
        // start/recurring-monthly-day trigger has come due (bugs.md,
        // 2026-10-20). Per-student idempotent via email_campaign_sends, so
        // safe alongside the daily cadence even if a run is missed/retried.
        $schedule->command('email-campaigns:dispatch-due --write')
            ->dailyAt('08:00')
            ->name('email-campaigns-dispatch-due')
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
