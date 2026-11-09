<?php

namespace App\Jobs;

use App\Models\Transfer;
use App\Services\BankTransferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Pays a bill (a transfers row) now. Scheduled bills are run by the scheduler's
 * transfers:run-due like scheduled transfers; this job is kept for anything
 * that still dispatches one directly.
 */
class ProcessScheduledBillPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transferId;

    public function __construct($transferId)
    {
        $this->transferId = $transferId;
    }

    public function handle()
    {
        // Only a bill still waiting, and only when due - the scheduler's
        // transfers:run-due pays it too, so anything else would pay it twice.
        $transfer = Transfer::find($this->transferId);
        if (!$transfer || $transfer->status !== 'scheduled') {
            return;
        }
        if ($transfer->kind !== 'bill') {
            $transfer->update(['kind' => 'bill']);
        }
        app(BankTransferService::class)->runScheduled($transfer->id);
    }
}
