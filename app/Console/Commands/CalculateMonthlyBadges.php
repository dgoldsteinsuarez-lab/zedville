<?php

namespace App\Console\Commands;

use App\Services\BadgeCalculatorService;
use Illuminate\Console\Command;
use Carbon\Carbon;

/**
 * ZEDVILLE — Engagement Badge
 * Command: CalculateMonthlyBadges
 *
 * INSTRUCTIONS FOR IT:
 * 1. Place this file in: app/Console/Commands/CalculateMonthlyBadges.php
 *
 * 2. Register it in the scheduler — open app/Console/Kernel.php
 *    and add inside the schedule() method:
 *
 *    // Run at 11:59 PM on the last day of every month
 *    $schedule->command('badges:calculate-monthly')
 *             ->monthlyOn(31, '23:59')   // handles months with fewer days automatically
 *             ->withoutOverlapping()
 *             ->runInBackground();
 *
 *    If you prefer a specific day (e.g. 1st of next month at midnight):
 *    $schedule->command('badges:calculate-monthly --previous-month')
 *             ->monthly()               // runs on 1st of each month
 *             ->at('00:05')
 *             ->withoutOverlapping();
 *
 * 3. Set up the Cron entry on your Hostinger server (one-time setup):
 *    - Log in to Hostinger hPanel
 *    - Go to: Advanced → Cron Jobs
 *    - Add a new cron job with:
 *        Schedule: Every minute  (* * * * *)
 *        Command:  cd /path/to/your/zedville && php artisan schedule:run >> /dev/null 2>&1
 *    - Replace /path/to/your/zedville with the actual path to your Laravel project root
 *    - This single Cron entry powers ALL Laravel scheduled tasks, not just badges
 *
 * 4. To run manually at any time (useful for testing or catching up):
 *    php artisan badges:calculate-monthly
 *    php artisan badges:calculate-monthly --month=3 --year=2026
 *    php artisan badges:calculate-monthly --student=42
 */
class CalculateMonthlyBadges extends Command
{
    protected $signature = 'badges:calculate-monthly
                            {--month= : Month to calculate (default: current month)}
                            {--year=  : Year to calculate (default: current year)}
                            {--previous-month : Calculate for the previous month instead}
                            {--student= : Calculate for a single student ID only}';

    protected $description = 'Calculate Engagement Badges for all students for a given month';

    public function __construct(private BadgeCalculatorService $calculator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = Carbon::now();

        // Determine which month to calculate
        if ($this->option('previous-month')) {
            $target = $now->copy()->startOfMonth()->subMonth(); // from the 1st: no overflow on the 31st
            $month  = (int) $target->month;
            $year   = (int) $target->year;
        } else {
            $month = (int) ($this->option('month') ?? $now->month);
            $year  = (int) ($this->option('year')  ?? $now->year);
        }

        $this->info("Zedville — Calculating Engagement Badges");
        $this->info("Month: {$month} / Year: {$year}");
        $this->line('');

        // Single student mode
        if ($studentId = $this->option('student')) {
            $this->info("Calculating for student ID: {$studentId}");
            $academicYear = $this->calculator->getAcademicYear($month, $year);
            $record = $this->calculator->calculateForStudent((int) $studentId, $month, $year, $academicYear);
            $this->info("Done. Monthly badge: {$record->monthly_badge} | Accumulated: {$record->accumulated_badge}");
            return Command::SUCCESS;
        }

        // All students mode
        $this->info("Calculating for all active students...");

        try {
            $this->calculator->calculateAllStudentsForMonth($month, $year);
            $this->info("Badge calculation complete for {$month}/{$year}.");
        } catch (\Exception $e) {
            $this->error("Badge calculation failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
