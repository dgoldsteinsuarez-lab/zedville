<?php

namespace App\Support;

use App\Models\User;

/**
 * How much of the bank a student sees (bank review, 2026-10-29: "it's for
 * kids but also for grade 11 and 12 students"). Everyone sends money like at
 * a real bank - account number or IBAN and the name. Scheduled transfers and
 * standing orders (later / every week, month or year) are for the older
 * students: grade levels Advance and Advance Economist (sort order 4 and up).
 */
class BankLevel
{
    public const ADVANCED_FROM = 4;

    public static function advanced(?User $user): bool
    {
        $level = optional(optional(optional($user)->gradeRelation)->grade)->level;

        return $level !== null && (int) $level->sort_order >= self::ADVANCED_FROM;
    }
}
