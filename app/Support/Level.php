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

    /**
     * How many real months a month of play is for the student's saved and
     * invested money (config zedville.turn_months): 3 in the Advance, 6 in
     * the Advance Economist, 1 before.
     */
    public static function turnMonths(?User $user): int
    {
        return max(1, (int) (config('zedville.turn_months')[self::order($user)] ?? 1));
    }

    /** For tests and long-running commands: forget what was looked up. */
    public static function flush(): void
    {
        self::$orders = [];
    }
}
