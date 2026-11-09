<?php

namespace App\Services\Finhero\CompoundInterest;

use App\Models\User;
use App\Services\MoneyFactory\Activities;
use App\Services\SavingsLedger;

/**
 * FinHero Task 8, the rule of 72 (Intermediate, compound interest; Daniel,
 * 2026-11-08): years to double money ≈ 72 ÷ the interest rate. Two rates to work
 * out, the rate a goal needs, and the student's own Money Market. The intro's
 * example (3%) is never asked.
 */
class RuleOf72
{
    /** Rates asked about, in %: 72 divides each, and none is 3 (the example) or the Money Market's. */
    public const RATES = [2, 6, 8, 9, 12];

    /** Goals, in years, to double the savings in. */
    public const GOALS = [6, 8, 9, 12];

    public static function stage(int $studentId): array
    {
        return ['intro' => []];
    }

    /** "18 years" and the like: the right answer, the rate itself, and 100 ÷ the rate. */
    private static function years(int $n): array
    {
        return array_map(fn ($v) => trans_choice('compound.rule72.a_years', $v, ['n' => $v]), [intdiv(72, $n), $n, (int) round(100 / $n)]);
    }

    public static function questions(int $studentId): array
    {
        $rand = Activities::random('task_8', $studentId);
        $rates = $rand->shuffle(self::RATES);
        $goal = self::GOALS[$rand->between(0, count(self::GOALS) - 1)];
        // the student's own Money Market rate: 5 % in the Intermediate, 6 % from its third month
        $mm = (int) round(SavingsLedger::rate('money_market', User::find($studentId)) * 100);
        $ask = fn (int $r) => Activities::choice($rand,
            __('compound.rule72.q_years', ['rate' => Activities::percent($r)]),
            self::years($r),
            ['why' => __('compound.rule72.why_years', ['r' => $r, 'years' => intdiv(72, $r)])]);

        return [
            $ask($rates[0]),
            $ask($rates[1]),
            Activities::choice($rand, __('compound.rule72.q_rate', ['years' => $goal]),
                array_map(fn ($v) => Activities::percent($v), [intdiv(72, $goal), $goal, (int) round(100 / $goal)]),
                ['why' => __('compound.rule72.why_rate', ['years' => $goal, 'r' => intdiv(72, $goal), 'rate' => Activities::percent(intdiv(72, $goal))])]),
            Activities::choice($rand, __('compound.rule72.q_mm', ['rate' => Activities::percent($mm)]),
                self::years($mm),
                ['why' => __('compound.rule72.why_mm', ['r' => $mm, 'years' => intdiv(72, $mm)])]),
        ];
    }
}
