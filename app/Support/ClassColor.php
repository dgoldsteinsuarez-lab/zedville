<?php

namespace App\Support;

/**
 * Daniel, 2026-10-21: "I cannot see which grade it is [on the Calendar],
 * it's a good idea to give colours to grades, but you need to sign it
 * somewhere" - every calendar_events writer previously hardcoded the same
 * blue regardless of class. One shared, deterministic class -> color
 * mapping so every event for the same class always gets the same color,
 * and the admin calendar's legend (built client-side from the same
 * palette, see admin/calendar/calendar.blade.php) always matches.
 */
class ClassColor
{
    private const PALETTE = [
        '#0053f9', '#e5484d', '#12b76a', '#f59e0b',
        '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16',
        '#f97316', '#6366f1',
    ];

    public static function hex(?int $classId): string
    {
        if ($classId === null) {
            return '#6b7280';
        }

        return self::PALETTE[$classId % count(self::PALETTE)];
    }
}
