<?php

namespace App\Jobs;

use App\Services\BankTransferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs a scheduled or recurring transfer if it is due - once per date
 * (BankTransferService::runScheduled). Nothing dispatches it any more: the
 * scheduler's transfers:run-due does this every minute. It used to schedule
 * its own next run, which never moved on (bank review, 2026-10-29: a
 * recurring transfer looped until the request died).
 */
class ProcessScheduledTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $transferId;

    public function __construct($transferId)
    {
        $this->transferId = (int) $transferId;
    }

    public function handle()
    {
        app(BankTransferService::class)->runScheduled($this->transferId);
    }
}
