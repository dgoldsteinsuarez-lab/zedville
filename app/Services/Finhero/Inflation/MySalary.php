<?php

namespace App\Services\Finhero\Inflation;

use App\Models\ConsumerSurvey;
use App\Services\MoneyFactory\Activities;
use App\Services\PriceRise;

/**
 * FinHero Task 11, what is my salary worth? (Intermediate; proposal «¿Cuánto
 * vale mi sueldo?»): the salary stays the same while prices rise 5%, so it buys
 * 100 ÷ 105 = 95.2% of what it did - purchasing power. A chart of what it buys
 * before and now, and four questions.
 */
class MySalary extends Quiz
{
    /** What the salary buys now, in % of before. */
    public static function part(): float
    {
        return round(100 / (1 + PriceRise::INDEX / 100), 1);
    }

    public static function stage(int $studentId): array
    {
        return [
            'intro' => ['salary' => zeds(ConsumerSurvey::salaryFor($studentId)), 'index' => self::index()],
            'part' => self::part(),
            'part_text' => PriceRise::pct(self::part(), false),
            'full_text' => PriceRise::pct(100, false),
        ];
    }

    public static function questions(int $studentId): array
    {
        $rand = Activities::random('task_11', $studentId);
        $salary = ConsumerSurvey::salaryFor($studentId);
        $base = 100;
        $cost = $base * (1 + PriceRise::INDEX / 100);
        $part = self::part();
        $need = round($salary * (1 + PriceRise::INDEX / 100), 2);
        $p = fn ($n) => PriceRise::pct($n, false);

        return [
            Activities::choice($rand, __('inflation.salary.q_buy', ['index' => self::index()]),
                [__('inflation.salary.a_less'), __('inflation.salary.a_same'), __('inflation.salary.a_more')],
                ['why' => __('inflation.salary.why_buy')]),
            Activities::choice($rand, __('inflation.salary.q_part', ['base' => zeds($base), 'cost' => zeds($cost)]),
                self::three($p($part), [$p(100 - PriceRise::INDEX), $p(100 + PriceRise::INDEX)]),
                ['why' => __('inflation.salary.why_part', ['base' => zeds($base), 'cost' => zeds($cost),
                    'ratio' => \App\Support\Money::number($part / 100, 3), 'part' => $p($part), 'lost' => $p(round(100 - $part, 1))])]),
            Activities::choice($rand, __('inflation.salary.q_need'),
                self::three(zeds($need), [zeds($salary), zeds($salary + PriceRise::INDEX)]),
                ['why' => __('inflation.salary.why_need', ['salary' => zeds($salary), 'index' => self::index(), 'need' => zeds($need)])]),
            Activities::choice($rand, __('inflation.salary.q_name'),
                [__('inflation.salary.a_power'), __('inflation.salary.a_rate'), __('inflation.salary.a_index')],
                ['why' => __('inflation.salary.why_name')]),
        ];
    }
}
