<?php

namespace App\Observers;

use App\Models\Transfer;
use App\Services\NotificationService;

/**
 * "Transfer scheduled" when a later transfer or standing order is set up.
 * The "Transfer sent" notice comes from BankTransferService once the money
 * has really moved (bank review, 2026-10-29: it was sent here, when the row
 * was created - "completed successfully" even when the transfer then failed).
 * Stored in English; App\Support\NotificationText shows it in the reader's
 * language.
 */
class TransferNotificationObserver
{
    public function __construct(private NotificationService $notifications) {}

    public function created(Transfer $transfer): void
    {
        if (! $transfer->user_id || ! in_array($transfer->type, ['later', 'recurring'], true)) {
            return;
        }

        $amount = number_format((float) $transfer->amount, 2);
        $to = $transfer->beneficiary_name ?: 'the recipient';

        $this->notifications->log(
            (int) $transfer->user_id,
            'Bank Account',
            'transfer_scheduled',
            'Scheduled Transfer Reminder',
            "Your {$transfer->type} transfer of Ƶ{$amount} to {$to} has been scheduled.",
            'calendar',
            'blue',
            $transfer
        );
    }
}
