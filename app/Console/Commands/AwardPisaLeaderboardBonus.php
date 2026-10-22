<?php

namespace App\Console\Commands;

use App\Services\FinheroPointService;
use App\Services\PisaLeaderboardService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-10-21: "What about the game, and if he scores between the
 * best?" -> "you can build that." A small FinHero bonus for finishing in
 * the top 3 of a PISA Practice cohort's (one school's one Grade Level)
 * monthly leaderboard - evaluated once, at month end, rather than the
 * moment someone takes the lead, since rank keeps changing all month as
 * other students play and there's nothing stable to reward mid-month.
 *
 * Scheduled alongside the existing Engagement badge calculation
 * (badges:calculate-monthly, lastDayOfMonth 23:59 in Kernel::schedule())
 * rather than inventing a second cron entry for "things that happen at
 * month end."
 */
class AwardPisaLeaderboardBonus extends Command
{
    protected $signature = 'pisa:award-leaderboard-bonus
                            {--month= : Month to award (default: current)}
                            {--year=  : Year to award (default: current)}';

    protected $description = 'Award a FinHero bonus to the top 3 of each PISA Practice leaderboard cohort for a month';

    // 1st place gets a little extra recognition; 2nd and 3rd still get a
    // real bonus, not a token one - my own call on the split, stated
    // plainly (Daniel's ask was "1-2 points" without committing to which
    // place gets which).
    private const BONUS_BY_PLACE = [1 => 2, 2 => 1, 3 => 1];

    public function __construct(
        private PisaLeaderboardService $leaderboardService,
        private FinheroPointService $pointService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = Carbon::now();
        $month = (int) ($this->option('month') ?? $now->month);
        $year = (int) ($this->option('year') ?? $now->year);

        $this->info('Zedville — PISA Leaderboard Bonus');
        $this->info("Month: {$month} / Year: {$year}");

        $cohorts = $this->leaderboardService->cohortsWithActivity($month, $year);
        $awarded = 0;

        foreach ($cohorts as $cohort) {
            $ranked = $this->leaderboardService->rankingFor($cohort->category_id, $cohort->sid, $month, $year);
            // One stable key per cohort (not per month) - the explicit
            // month/year WHERE below is what actually prevents a re-run
            // from double-paying, matching every other monthly-cap check
            // built this week rather than baking the period into the key
            // itself.
            $sourceKey = 'pisa_leaderboard_cat' . $cohort->category_id . '_sid' . $cohort->sid;

            foreach ($ranked->take(3) as $index => $row) {
                $place = $index + 1;

                $alreadyAwarded = DB::table('finhero_student_points')
                    ->where('student_id', $row['student']->id)
                    ->where('source_type', 'activity')
                    ->where('source_key', $sourceKey)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->exists();

                if ($alreadyAwarded) {
                    continue;
                }

                $this->pointService->addPoints(
                    $row['student']->id,
                    'activity',
                    $sourceKey,
                    self::BONUS_BY_PLACE[$place],
                    null,
                    $month,
                    $year
                );
                $awarded++;
            }
        }

        $this->info("Awarded {$awarded} leaderboard bonus" . ($awarded === 1 ? '' : 'es') . '.');

        return Command::SUCCESS;
    }
}
