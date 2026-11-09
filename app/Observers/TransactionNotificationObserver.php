<?php

namespace App\Observers;

use App\Models\Transaction1;
use App\Services\NotificationService;

/**
 * A bell notification for a line on the current account. Stored in English;
 * App\Support\NotificationText writes it in the reader's language when shown.
 * Bank review, 2026-10-29: every credit was "Direct Deposit Received" (a
 * classmate's transfer, money back from savings, a reversal), and a move
 * between the student's own accounts gave two notices.
 */
class TransactionNotificationObserver
{
    public const LARGE = 500;

    public function __construct(private NotificationService $notifications) {}

    public function created(Transaction1 $txn): void
    {
        if (! $txn->user_id || (float) $txn->amount == 0.0) {
            return; // e.g. the "Autopilot - [Month]" history line
        }

        $isCredit = strtolower((string) $txn->type) === 'credit';

        // Moves between the student's own accounts, and a transfer they sent
        // (that one gets its own "Transfer sent" notice from BankTransferService).
        if ($txn->category === 'Own Transfer' || ($txn->category === 'Fund Transfer' && ! $isCredit)) {
            return;
        }

        $amount = number_format((float) $txn->amount, 2);

        if ($isCredit) {
            $title = $txn->category === 'Income' ? 'Salary Received' : 'Money Received';
            $body = "{$txn->description}: +Ƶ{$amount} has been added to your account.";
            $icon = 'dollar-sign';
            $color = 'green';
        } elseif ((float) $txn->amount >= self::LARGE) {
            $title = 'Large Transaction Alert';
            $body = "A transaction of Ƶ{$amount} was processed for {$txn->description}.";
            $icon = 'credit-card';
            $color = 'blue';
        } else {
            $title = 'Transaction Alert';
            $body = "{$txn->description}: -Ƶ{$amount} from your account.";
            $icon = 'credit-card';
            $color = 'blue';
        }

        $this->notifications->log(
            (int) $txn->user_id,
            'Bank Account',
            'transaction',
            $title,
            $body,
            $icon,
            $color,
            $txn
        );
    }
}
