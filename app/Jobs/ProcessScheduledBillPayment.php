<?php

namespace App\Jobs;

use App\Http\Controllers\BankController;
use App\Models\Transfer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        $transfer = Transfer::find($this->transferId);
        if (!$transfer) {
            return;
        }

        // Use the real controller method (sid, row lock, floor-at-zero)
        // instead of the divergent copy this job used to carry.
        (new BankController())->processBillPayment(
            $transfer->user_id,
            $transfer->amount,
            $transfer->beneficiary_name,
            $transfer->account_number
        );
    }
}
