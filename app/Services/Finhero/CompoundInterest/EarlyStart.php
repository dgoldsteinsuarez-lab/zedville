<?php

namespace App\Services\Finhero\CompoundInterest;

use App\Models\User;
use App\Services\MoneyFactory\Activities;
use App\Services\SavingsLedger;

/**
 * FinHero Task 7, who will have more? (Intermediate; Daniel, 2026-11-08: "la
 * segunda empieza con bastante más dinero de inversión, pero se queda con menos
 * ahorrado porque empezó más tarde"). Ana saves every month from 15; Bea from
 * 25, quite a lot more each month (about 1.7 times); both until 45 at the
 * student's own Money Market rate (5 % in the Intermediate, 6 % from its third month). Bea puts in more, Ana ends with more. A chart of what each put
 * in and the interest, and four questions to choose an answer for.
 */
class EarlyStart
{
    /** Ana's monthly saving; Bea's is about 1.7 times it, rounded to tens - below what would catch up (1.89 at 4 %, more at higher rates). */
    public const MONTHLY = [50, 100, 150];
    public const BEA_TIMES = 1.7;
    public const ANA = 15;
    public const BEA = 25;
    public const END = 45;

    /** [Ana's, Bea's] monthly saving for a student. */
    private static function monthly(int $studentId): array
    {
        $rand = Activities::random('task_7', $studentId);
        $ana = self::MONTHLY[$rand->between(0, count(self::MONTHLY) - 1)];

        return [$ana, (int) (round($ana * self::BEA_TIMES / 10) * 10)];
    }

    /** Saving $monthly every month from $from to $to years old at a yearly $rate, the interest added every month. */
    public static function saved(int $monthly, int $from, int $to, float $rate): float
    {
        $r = $rate / 12;
        $months = ($to - $from) * 12;

        return round($monthly * (((1 + $r) ** $months - 1) / $r), 2);
    }

    /** The student's Money Market rate, as a fraction (SavingsLedger::rate). */
    public static function rate(int $studentId): float
    {
        return SavingsLedger::rate('money_market', User::find($studentId));
    }

    /** Each one's monthly saving, what she put in and what she has at 45. */
    private static function people(int $studentId): array
    {
        [$ana, $bea] = self::monthly($studentId);
        $rate = self::rate($studentId);

        return [
            'Ana' => ['from' => self::ANA, 'monthly' => $ana, 'put' => $ana * 12 * (self::END - self::ANA), 'total' => self::saved($ana, self::ANA, self::END, $rate)],
            'Bea' => ['from' => self::BEA, 'monthly' => $bea, 'put' => $bea * 12 * (self::END - self::BEA), 'total' => self::saved($bea, self::BEA, self::END, $rate)],
        ];
    }

    public static function stage(int $studentId): array
    {
        $people = self::people($studentId);
        $top = max(array_column($people, 'total'));

        return [
            'intro' => ['ana' => zeds($people['Ana']['monthly']), 'bea' => zeds($people['Bea']['monthly']), 'rate' => Activities::percent(self::rate($studentId) * 100)],
            'people' => array_map(fn ($p, $name) => $p + [
                'name' => $name,
                'monthly_text' => zeds($p['monthly']),
                'total_text' => zeds($p['total']),
                'put_pct' => round($p['put'] / $top * 100, 1),
                'interest_pct' => round(($p['total'] - $p['put']) / $top * 100, 1),
            ], $people, array_keys($people)),
        ];
    }

    public static function questions(int $studentId): array
    {
        ['Ana' => $ana, 'Bea' => $bea] = self::people($studentId);
        $extra = $bea['put'] - $ana['put'];
        $diff = round($ana['total'] - $bea['total'], 2);
        $totals = ['ana_total' => zeds($ana['total']), 'bea_total' => zeds($bea['total'])];
        $rand = Activities::random('task_7:questions', $studentId);
        $who = fn (string $right, string $other) => [$right, $other, __('compound.early.a_same')];

        return [
            Activities::choice($rand, __('compound.early.q_put'), $who('Bea', 'Ana'),
                ['why' => __('compound.early.why_put', ['bea_put' => zeds($bea['put']), 'ana_put' => zeds($ana['put']), 'extra' => zeds($extra)])]),
            Activities::choice($rand, __('compound.early.q_who'), $who('Ana', 'Bea'),
                ['why' => __('compound.early.why_who', $totals)]),
            Activities::choice($rand, __('compound.early.q_more'), [zeds($diff), zeds($extra), zeds($ana['total'])],
                ['why' => __('compound.early.why_more', $totals + ['diff' => zeds($diff)])]),
            Activities::choice($rand, __('compound.early.q_lesson'),
                [__('compound.early.a_lesson_early'), __('compound.early.a_lesson_rate'), __('compound.early.a_lesson_luck')],
                ['why' => __('compound.early.why_lesson')]),
        ];
    }
}
