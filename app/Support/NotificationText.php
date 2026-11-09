<?php

namespace App\Support;

use App\Models\AppNotification;
use App\Models\Transaction1;
use App\Models\Transfer;

/**
 * A notification in the reader's language. Notifications are stored in
 * English; the bank's (bank review, 2026-10-29), the mood's and the badges'
 * (Daniel, 2026-10-30: "mood and badges can be in the language to learn")
 * are written again from their source when shown, so a Spanish student reads
 * Spanish - and a hybrid class gets them mixed like the rest of its pages.
 * Any other notification is shown as stored.
 */
class NotificationText
{
    /** @return array{0: string, 1: ?string} title and body */
    public static function for(AppNotification $notification): array
    {
        $source = $notification->source_id ? $notification->source : null;

        if ($source instanceof Transaction1) {
            return self::line($source);
        }
        if ($source instanceof Transfer) {
            return self::transfer($source, $notification->type);
        }
        if ($source instanceof \App\Models\MoodLog) {
            return [__('notifications.mood_title'), __('notifications.mood_body', ['mood' => self::mood((string) $source->mood)])];
        }
        // the level when it was earned, from the stored text (a month can be recalculated later)
        $level = preg_match('/earned the (\w+) /i', (string) $notification->body, $m) ? strtolower($m[1]) : null;
        if ($source instanceof \App\Models\StudentBadgeRecord && $level) {
            return [__('notifications.engagement_badge_title'), __('notifications.engagement_badge_body', ['badge' => self::level('levels', $level)])];
        }
        if ($source instanceof \App\Models\FinheroBadgeRecord && $level) {
            return [__('notifications.finhero_badge_title'), __('notifications.finhero_badge_body', ['badge' => self::level('fh_levels', $level)])];
        }

        return [$notification->title, $notification->body];
    }

    private static function mood(string $mood): string
    {
        $key = 'layout.mood.' . str_replace(' ', '_', strtolower(trim($mood)));
        $name = __($key);

        return $name === $key ? ucfirst($mood) : $name;
    }

    private static function level(string $list, string $level): string
    {
        $key = 'badges.js.' . $list . '.' . $level;
        $name = __($key);

        return $name === $key ? ucfirst($level) : $name;
    }

    private static function line(Transaction1 $line): array
    {
        $description = txn_text($line->description);
        $amount = zeds($line->amount);

        if ($line->type === 'credit') {
            $title = match ($line->category) {
                'Income' => __('notifications.salary_title'),
                default => __('notifications.money_received_title'),
            };

            return [$title, __('notifications.credit_body', ['description' => $description, 'amount' => $amount])];
        }

        $title = (float) $line->amount >= \App\Observers\TransactionNotificationObserver::LARGE
            ? __('notifications.large_payment_title')
            : __('notifications.payment_title');

        return [$title, __('notifications.debit_body', ['description' => $description, 'amount' => $amount])];
    }

    private static function transfer(Transfer $transfer, ?string $type): array
    {
        $name = $transfer->targetLabel(\App\Models\BankAccount::where('student_id', $transfer->user_id)->first());
        $amount = zeds($transfer->amount);

        if ($type === 'transfer_scheduled') {
            $first = $transfer->next_run_at ?? $transfer->scheduled_at ?? $transfer->start_date;
            $date = $first ? $first->copy()->locale(app()->getLocale())->isoFormat('LL') : '';
            $key = $transfer->type === 'recurring' ? 'notifications.standing_order_body' : 'notifications.transfer_scheduled_body';

            return [__('notifications.transfer_scheduled_title'), __($key, ['amount' => $amount, 'name' => $name, 'date' => $date])];
        }

        return [__('notifications.transfer_done_title'), __('notifications.transfer_done_body', ['amount' => $amount, 'name' => $name])];
    }
}
