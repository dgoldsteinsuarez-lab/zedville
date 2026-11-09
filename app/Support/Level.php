<?php

namespace App\Support;

use App\Models\User;

/**
 * The level of a student's class (grade_levels, by sort_order: Junior 1,
 * Rookie 2, Intermediate 3, Advance 4, Advance Economist 5). The Intermediate
 * and the levels after it save as much as they can instead of sharing the
 * salary 50/30/20 (Daniel, 2026-11-03/08): the share for the Emergency Fund is
 * theirs to choose, the Money Market asks only for the month's tasks, Spending
 * 4 is about 15% of the salary and "How much have I saved?" replaces the Match.
 */
class Level
{
    public const INTERMEDIATE = 3;

    private static array $orders = [];

    /** The class level's sort order; 0 without one. */
    public static function order(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        return self::$orders[$user->id] ??= (int) optional(optional(optional($user->gradeRelation)->grade)->level)->sort_order;
    }

    /** Intermediate or a later level. */
    public static function saver(?User $user): bool
    {
        return self::order($user) >= self::INTERMEDIATE;
    }

    /** For tests and long-running commands: forget what was looked up. */
    public static function flush(): void
    {
        self::$orders = [];
    }
}
