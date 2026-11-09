<?php
// ============================================================
// FILE: app/Console/Commands/CalculateFinheroMonthlyBadges.php
// ============================================================
// INSTRUCTIONS:
// 1. Place in app/Console/Commands/
// 2. Register in app/Console/Kernel.php inside schedule():
//
//    $schedule->command('finhero:calculate-monthly')
//             ->monthlyOn(31, '23:59')
//             ->withoutOverlapping()
//             ->runInBackground();
//
// 3. Cron entry on Hostinger (one-time, same as Engagement Badge):
//    * * * * * cd /path/to/zedville && php artisan schedule:run >> /dev/null 2>&1
//
// Manual run examples:
//   php artisan finhero:calculate-monthly
//   php artisan finhero:calculate-monthly --month=4 --year=2026
//   php artisan finhero:calculate-monthly --student=42

namespace App\Console\Commands;

use App\Services\FinheroBadgeCalculatorService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CalculateFinheroMonthlyBadges extends Command
{
    protected $signature = 'finhero:calculate-monthly
                            {--month= : Month to calculate (default: current)}
                            {--year=  : Year to calculate (default: current)}
                            {--previous-month : Calculate for the previous month}
                            {--student= : Calculate for a single student ID only}';

    protected $description = 'Calculate FinHero Badges for all students for a given month';

    public function __construct(private FinheroBadgeCalculatorService $calculator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = Carbon::now();

        if ($this->option('previous-month')) {
            $target = $now->copy()->startOfMonth()->subMonth(); // from the 1st: no overflow on the 31st
            $month  = (int) $target->month;
            $year   = (int) $target->year;
        } else {
            $month = (int) ($this->option('month') ?? $now->month);
            $year  = (int) ($this->option('year')  ?? $now->year);
        }

        $this->info("Zedville — FinHero Badge Calculation");
        $this->info("Month: {$month} / Year: {$year}");

        if ($studentId = $this->option('student')) {
            $record = $this->calculator->calculateForStudent((int) $studentId, $month, $year);
            $this->info("Done. Badge: {$record->monthly_badge} ({$record->monthly_pct}%) | Accumulated: {$record->accumulated_badge}");
            return Command::SUCCESS;
        }

        try {
            $this->calculator->calculateAllStudentsForMonth($month, $year);
            $this->info("FinHero badge calculation complete for {$month}/{$year}.");
        } catch (\Exception $e) {
            $this->error("Calculation failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}



