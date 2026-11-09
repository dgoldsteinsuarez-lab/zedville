<?php

namespace App\Console\Commands;

use App\Services\ActivityFineService;
use App\Support\CalendarTitle;
use Illuminate\Console\Command;

/**
 * The fines for Required activities not done in time (App\Services\ActivityFineService),
 * every day just after midnight - the day after a deadline. Default is a dry run:
 *   php artisan fines:apply            preview (writes nothing)
 *   php artisan fines:apply --write    really fine
 */
class ApplyActivityFines extends Command
{
    protected $signature = 'fines:apply {--write}';
    protected $description = 'Fine students for Required activities not done in time (dry run unless --write)';

    public function handle(ActivityFineService $fines)
    {
        $write = (bool) $this->option('write');
        $done = $fines->run(now(), $write);

        foreach ($done as $fine) {
            $this->line(sprintf('  %s: %s (%s Ƶ)%s', $fine['student']->nickname ?: $fine['student']->name,
                CalendarTitle::forStudent($fine['event']->title), number_format($fine['amount'], 2), $write ? '' : ' - would be fined'));
        }
        $this->comment($write ? "Fined {$done->count()} time(s)." : "Dry run - {$done->count()} fine(s) found, nothing written. Add --write to apply.");

        return self::SUCCESS;
    }
}
