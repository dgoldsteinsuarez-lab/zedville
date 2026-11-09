<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * An activity's dates as an admin assigned them (first and last day, either
 * may be open). Daniel, 2026-11-03: "reset all the activities that is showing
 * when the deadline already ended" - the Education Finance Department board
 * shows an activity only while it runs, with the days left to do it.
 */
class Deadline
{
    /** Whether it runs today: from its first to its last day. */
    public static function running($start, $end, ?Carbon $today = null): bool
    {
        $today = ($today ?? now())->copy()->startOfDay();

        return (!$start || Carbon::parse($start)->startOfDay()->lte($today))
            && (!$end || Carbon::parse($end)->startOfDay()->gte($today));
    }

    /** The days left to do it, today included (1 on its last day); null without a last day. */
    public static function daysLeft($end, ?Carbon $today = null): ?int
    {
        if (!$end) {
            return null;
        }
        $today = ($today ?? now())->copy()->startOfDay();

        return max(0, (int) $today->diffInDays(Carbon::parse($end)->startOfDay(), false) + 1);
    }

    /** "5 days left", "Last day: today" - or null without a last day. */
    public static function label($end, ?Carbon $today = null): ?string
    {
        $days = self::daysLeft($end, $today);

        return match (true) {
            $days === null || $days === 0 => null,
            $days === 1 => __('efd.last_day'),
            default => trans_choice('efd.days_left', $days, ['count' => $days]),
        };
    }
}
