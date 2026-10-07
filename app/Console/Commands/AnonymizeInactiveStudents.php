<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Admin Roles & Permissions doc §7: "If a student does not continue to the
 * next level or becomes inactive, data should be anonymised or deleted
 * within 12 months of last activity."
 *
 * This is a reasonable technical default (plain login-inactivity), not a
 * legal sign-off - confirm the exact retention trigger with the school
 * before relying on this alone for GDPR/BDSG compliance.
 *
 * Anonymizes the account only - never touches Transaction1/badge/petition
 * history, matching this codebase's append-only ledger philosophy
 * elsewhere (LedgerController, AutopilotPeriod). `anonymized_at` is both
 * the audit marker and the idempotency check.
 *
 *   php artisan students:anonymize-inactive            dry run (default)
 *   php artisan students:anonymize-inactive --write    actually anonymizes
 */
class AnonymizeInactiveStudents extends Command
{
    protected $signature = 'students:anonymize-inactive {--write}';
    protected $description = 'Anonymize student accounts inactive for 12+ months (never touches ledger/badge history)';

    public function handle()
    {
        $write = (bool) $this->option('write');
        $cutoff = now()->subMonths(12);

        $candidates = User::where('role', 4)
            ->whereNull('anonymized_at')
            ->where(function ($q) use ($cutoff) {
                $q->where(function ($q2) use ($cutoff) {
                    $q2->whereNotNull('loginTime')->where('loginTime', '<', $cutoff);
                })->orWhere(function ($q2) use ($cutoff) {
                    $q2->whereNull('loginTime')->where('created_at', '<', $cutoff);
                });
            })
            ->get();

        if ($candidates->isEmpty()) {
            $this->comment('No students past the 12-month inactivity threshold.');
            return self::SUCCESS;
        }

        foreach ($candidates as $student) {
            $this->line('  #' . $student->id . ' (' . $student->nickname . '): ' . ($write ? 'anonymizing' : 'would anonymize'));

            if ($write) {
                $student->update([
                    'name' => 'Former Student',
                    'nickname' => 'anon-' . $student->id,
                    'email' => 'anon-' . $student->id . '@zedville.invalid',
                    'address' => null,
                    'anonymized_at' => now(),
                ]);
            }
        }

        $this->comment($write
            ? 'Anonymized ' . $candidates->count() . ' account(s).'
            : 'Dry run - would anonymize ' . $candidates->count() . ' account(s). Add --write to apply.');

        return self::SUCCESS;
    }
}
