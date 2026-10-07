<?php

namespace App\Console\Commands;

use App\Models\Petition;
use Illuminate\Console\Command;

/**
 * Per the Admin Roles & Permissions doc §6: "Petitions are automatically
 * archived and deleted after 3 months" / "If a Tutor does not review a
 * petition, it remains queued indefinitely until reviewed or until the
 * 3-month expiry." We close rather than delete — deleting would also wipe
 * `petition_signatures` history for no benefit, and this codebase's
 * convention elsewhere (the ledger, AutopilotPeriod) is append-only /
 * status-driven rather than destructive.
 *
 *   php artisan petitions:expire-stale            dry run (default) - lists what it would close
 *   php artisan petitions:expire-stale --write    actually closes them
 */
class ExpirePetitions extends Command
{
    protected $signature = 'petitions:expire-stale {--write}';
    protected $description = 'Close petitions still pending 3+ months after submission (no money/data moves)';

    public function handle()
    {
        $write = (bool) $this->option('write');
        $cutoff = now()->subMonths(3);

        $stale = Petition::where('status', 'pending')->where('created_at', '<=', $cutoff)->get();

        if ($stale->isEmpty()) {
            $this->comment('No stale pending petitions found.');
            return self::SUCCESS;
        }

        foreach ($stale as $petition) {
            $this->line('  #' . $petition->id . ' "' . $petition->title . '" (submitted ' . $petition->created_at->toDateString() . '): ' . ($write ? 'closing' : 'would close'));

            if ($write) {
                $petition->update([
                    'status' => 'closed',
                    'tutor_feedback' => $petition->tutor_feedback ?: 'Automatically closed after 3 months without review.',
                ]);
            }
        }

        $this->comment($write
            ? 'Closed ' . $stale->count() . ' stale petition(s).'
            : 'Dry run - nothing written. Add --write to close them.');

        return self::SUCCESS;
    }
}
