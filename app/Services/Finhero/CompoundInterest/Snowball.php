<?php

namespace App\Services\Finhero\CompoundInterest;

use App\Models\User;
use App\Services\MoneyFactory\Activities;
use App\Services\SavingsLedger;

/**
 * FinHero Task 6, the snowball: compound interest (Intermediate; Daniel,
 * 2026-11-08: "intermediate, should have compound interest activities, simple
 * ones, so that they can see how much all growth"). An amount left alone at
 * the student's own Money Market rate (5 % in the Intermediate, 6 % from its
 * third month: "me gustaría que fuera 5 % para que vean mejor el efecto"): what
 * it is after 1, 5, 10 and 20 years, next to what simple interest would give -
 * a bar chart - and three questions: the money in 10 years, the interest in
 * 20, and why it grows faster and faster.
 */
class Snowball
{
    public const AMOUNTS = [500, 1000, 1500, 2000];
    public const YEARS = [1, 5, 10, 20];

    private static function amount(int $studentId): int
    {
        $rand = Activities::random('task_6', $studentId);

        return self::AMOUNTS[$rand->between(0, count(self::AMOUNTS) - 1)];
    }

    /** The yearly rate, as a fraction: the student's Money Market's (SavingsLedger::rate). */
    public static function rate(int $studentId): float
    {
        return SavingsLedger::rate('money_market', User::find($studentId));
    }

    /** With the interest added every year. */
    public static function compound(float $amount, int $years, float $rate): float
    {
        return round($amount * (1 + $rate) ** $years, 2);
    }

    /** If the interest earned no interest. */
    public static function simple(float $amount, int $years, float $rate): float
    {
        return round($amount * (1 + $rate * $years), 2);
    }

    public static function stage(int $studentId): array
    {
        $amount = self::amount($studentId);
        $rate = self::rate($studentId);
        $top = self::compound($amount, max(self::YEARS), $rate);

        return [
            'intro' => ['amount' => zeds($amount), 'rate' => Activities::percent($rate * 100)],
            'rows' => array_map(fn ($years) => [
                'years' => $years,
                'compound' => zeds(self::compound($amount, $years, $rate)),
                'simple' => zeds(self::simple($amount, $years, $rate)),
                'compound_pct' => round(self::compound($amount, $years, $rate) / $top * 100, 1),
                'simple_pct' => round(self::simple($amount, $years, $rate) / $top * 100, 1),
            ], self::YEARS),
        ];
    }

    public static function questions(int $studentId): array
    {
        $amount = self::amount($studentId);
        $r = self::rate($studentId);
        $rate = Activities::percent($r * 100);
        $rand = Activities::random('task_6:questions', $studentId);

        return [
            Activities::choice($rand, __('compound.snowball.q_ten', ['amount' => zeds($amount)]),
                [zeds(self::compound($amount, 10, $r)), zeds(self::simple($amount, 10, $r)), zeds(self::compound($amount, 1, $r))],
                ['why' => __('compound.snowball.why_ten', ['rate' => $rate, 'amount' => zeds($amount), 'result' => zeds(self::compound($amount, 10, $r))])]),
            Activities::choice($rand, __('compound.snowball.q_interest'),
                [zeds(self::compound($amount, 20, $r) - $amount), zeds(self::simple($amount, 20, $r) - $amount), zeds($amount * $r)],
                ['why' => __('compound.snowball.why_interest', ['interest' => zeds(self::compound($amount, 20, $r) - $amount), 'simple' => zeds(self::simple($amount, 20, $r) - $amount)])]),
            Activities::choice($rand, __('compound.snowball.q_why'),
                [__('compound.snowball.a_why_interest'), __('compound.snowball.a_why_rate'), __('compound.snowball.a_why_more')],
                ['why' => __('compound.snowball.why_why')]),
        ];
    }
}
