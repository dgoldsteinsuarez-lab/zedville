<?php

namespace App\Services;

/**
 * Zedville_FinHero_Point_Values_v1.pdf §2 "The Approach System": the tiered
 * scoring pattern applied to every scored question/unit across Tasks 1-5.
 * Single source of truth for the tier table so task views and the backend
 * agree on the same numbers - the frontend tracks which tier a student is
 * on (attempts are purely a UX/hint-escalation concern there), but the
 * actual POINT VALUE for a tier is always resolved here, server-side, same
 * principle as FinheroStudentController::awardPoints() never trusting a
 * raw point number from the browser.
 */
class FinheroApproachSystem
{
    public const TIERS = [
        0 => ['max_wrong_attempts' => 2, 'points' => 3], // First Approach - no help
        1 => ['max_wrong_attempts' => 2, 'points' => 2], // Second Approach - light hint
        2 => ['max_wrong_attempts' => 2, 'points' => 1], // Third Approach - fuller hint
        3 => ['max_wrong_attempts' => null, 'points' => 0], // Beyond Third - unlimited, no points
    ];

    public static function pointsForTier(int $tier): int
    {
        return self::TIERS[$tier]['points'] ?? 0;
    }

    public static function isValidTier(int $tier): bool
    {
        return array_key_exists($tier, self::TIERS);
    }
}
