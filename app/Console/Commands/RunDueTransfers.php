<?php

namespace App\Console\Commands;

use App\Models\Transfer;
use App\Services\BankTransferService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Runs the scheduled and recurring transfers and bill payments that are due,
 * each date once (BankTransferService::runScheduled). Every minute from the
 * scheduler. Replaces the old every-minute job that ran every past one again
 * each minute (bank review, 2026-10-29).
 */
class RunDueTransfers extends Command
{
    protected $signature = 'transfers:run-due';

    protected $description = 'Run the scheduled and recurring transfers and bill payments that are due';

    public function handle(BankTransferService $bank)
    {
        $ids = Transfer::where('status', 'scheduled')->where('next_run_at', '<=', now())->orderBy('next_run_at')->pluck('id');
        $done = 0;
        foreach ($ids as $id) {
            try {
                if ($bank->runScheduled($id) !== null) {
                    $done++;
                }
            } catch (\Throwable $e) {
                // one broken transfer must not stop the others
                Log::error('Scheduled transfer failed', ['transfer_id' => $id, 'error' => $e->getMessage()]);
                Transfer::whereKey($id)->update(['status' => 'failed', 'failure' => 'transfer_failed']);
            }
        }
        $this->line("Ran {$done} due transfer(s).");

        return self::SUCCESS;
    }
}
