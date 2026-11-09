<?php

namespace App\Services;

use App\Models\SavingsTransaction;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;

/**
 * The Intermediate's two scenarios, per student and on their own (Daniel,
 * 2026-11-08: "debería hacerse por estudiante ... del rookie, muchos pasarían
 * al intermediate el siguiente año ... [una] cuenta nueva, se le daría el B"):
 * A, the student brings an Emergency Fund with money from the Rookie; B, they
 * start without one - a new account, or none saved - so their emergencies come
 * later, counted from when they arrived. The level's months count from the
 * month the class started it: the one an admin sets (Admin > Intermediate >
 * Intermediate setup) or, by default, the start of the school year.
 */
class IntermediateClassService
{
    /** The month the class started the Intermediate. */
    public static function start(SchoolClass $class, ?Carbon $now = null): Carbon
    {
        if ($class->intermediate_started_on) {
            return $class->intermediate_started_on->copy()->startOfMonth();
        }
        $now ??= now();
        $month = (int) config('zedville.academic_year_start_month', 4);

        return Carbon::create($now->month >= $month ? $now->year : $now->year - 1, $month, 1);
    }

    /** The class's month in the level: 1 the month it started; null before it. */
    public static function month(SchoolClass $class, ?Carbon $now = null): ?int
    {
        $now ??= now();

        return self::monthsSince(self::start($class, $now), $now);
    }

    /** A: an Emergency Fund with money in it before the class started the level; B otherwise. */
    public static function scenarioFor(User $user, ?SchoolClass $class = null, ?Carbon $now = null): string
    {
        $class ??= $user->grade ? SchoolClass::find($user->grade) : null;
        $before = $class ? self::start($class, $now) : ($now ?? now())->copy()->startOfMonth();
        $balance = SavingsTransaction::where('user_id', $user->id)->where('account', 'emergency')
            ->where('transaction_date', '<', $before)
            ->orderByDesc('transaction_date')->orderByDesc('id')
            ->value('balance');

        return (float) $balance > 0 ? 'A' : 'B';
    }

    /**
     * The student's own month in the level, for what is theirs (their
     * emergencies): the class's for A; for B, from when they arrived if that
     * was after the class started.
     */
    public static function studentMonth(User $user, ?Carbon $now = null): ?int
    {
        $class = $user->grade ? SchoolClass::find($user->grade) : null;
        if (!$class) {
            return null;
        }
        $now ??= now();
        $from = self::start($class, $now);
        if (self::scenarioFor($user, $class, $now) === 'B' && $user->created_at && $user->created_at->copy()->startOfMonth()->gt($from)) {
            $from = $user->created_at->copy()->startOfMonth();
        }

        return self::monthsSince($from, $now);
    }

    private static function monthsSince(Carbon $from, Carbon $now): ?int
    {
        $months = ($now->year - $from->year) * 12 + ($now->month - $from->month);

        return $months < 0 ? null : $months + 1;
    }
}
