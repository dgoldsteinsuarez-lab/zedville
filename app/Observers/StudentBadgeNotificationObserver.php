<?php

namespace App\Observers;

use App\Models\StudentBadgeRecord;
use App\Services\NotificationService;

class StudentBadgeNotificationObserver
{
    public function __construct(private NotificationService $notifications) {}

    public function saved(StudentBadgeRecord $record): void
    {
        // a month's first calculation creates the record - it has to count too
        // (2026-10-30: only an update did, so the notice almost never came)
        if (! $record->wasRecentlyCreated && ! $record->wasChanged('monthly_badge')) {
            return;
        }

        $badge = strtoupper((string) $record->monthly_badge);
        if ($badge === '' || $badge === 'NONE') {
            return;
        }

        $this->notifications->log(
            (int) $record->student_id,
            'Education Finance Department',
            'badge_earned',
            'Engagement Badge Earned',
            "You earned the {$record->monthly_badge} engagement badge this month!",
            'award',
            'amber',
            $record
        );
    }
}
