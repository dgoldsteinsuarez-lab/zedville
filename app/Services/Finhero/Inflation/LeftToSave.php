<?php

namespace App\Services\Finhero\Inflation;

use App\Models\ConsumerSurvey;
use App\Services\DonationService;
use App\Services\InsuranceService;
use App\Services\MoneyFactory\Activities;
use App\Services\PriceRise;
use App\Services\WantsPlanService;

/**
 * FinHero Task 12, how much is left to save? (Intermediate; proposal «¿Cuánto
 * ahorro ahora?»): the student's month - bills, the survey's shopping, about
 * 15% for the plan of the month and its spending activity, the donation, the
 * health insurance - at the old prices and the new ones. The salary stays, so
 * less is left to save.
 */
class LeftToSave extends Quiz
{
    /** The month's lines, ['before', 'after'] each, and the salary. */
    public static function month(int $studentId): array
    {
        $salary = ConsumerSurvey::salaryFor($studentId);
        $bills = PriceRise::bills();
        $shopping = MyBasket::lines($studentId)[0];
        $plan = round(WantsPlanService::MID_TOTAL * $salary, 2);
        $donation = round((float) optional(DonationService::active($studentId))->amount, 2);
        $sum = fn (array $lines, string $key) => round(array_sum(array_column($lines, $key)), 2);

        $lines = [
            'bills' => ['before' => $sum($bills, 'before'), 'after' => $sum($bills, 'after')],
            'shopping' => ['before' => $sum($shopping, 'before'), 'after' => $sum($shopping, 'after')],
            'plan' => ['before' => $plan, 'after' => PriceRise::risen($plan, PriceRise::PLAN)],
        ];
        if ($donation > 0) {
            // a donation is a standing order: it doesn't rise
            $lines['donation'] = ['before' => $donation, 'after' => $donation];
        }
        // nor does the health insurance's premium (App\Services\InsuranceService)
        if ($policy = InsuranceService::current($studentId)) {
            $premium = InsuranceService::PLANS[$policy->plan]['premium'];
            $lines['insurance'] = ['before' => $premium, 'after' => $premium];
        }

        return [$lines, $salary];
    }

    /** What is spent and left, before and after, and the left's share of the salary. */
    private static function totals(int $studentId): array
    {
        [$lines, $salary] = self::month($studentId);
        $before = round(array_sum(array_column($lines, 'before')), 2);
        $after = round(array_sum(array_column($lines, 'after')), 2);

        return [
            'salary' => $salary, 'before' => $before, 'after' => $after, 'extra' => round($after - $before, 2),
            'left_before' => round($salary - $before, 2), 'left' => round($salary - $after, 2),
            'share_before' => round(($salary - $before) / $salary * 100, 1), 'share' => round(($salary - $after) / $salary * 100, 1),
        ];
    }

    public static function stage(int $studentId): array
    {
        [$lines] = self::month($studentId);
        $t = self::totals($studentId);

        return [
            'intro' => ['salary' => zeds($t['salary'])],
            'rows' => collect($lines)->map(fn ($line, $key) => ['name' => __('inflation.left.' . $key), 'before' => zeds($line['before']), 'after' => zeds($line['after'])])->values()->all(),
            'spent' => ['before' => zeds($t['before']), 'after' => zeds($t['after'])],
            'left' => ['before' => zeds($t['left_before']), 'after' => zeds($t['left'])],
        ];
    }

    public static function questions(int $studentId): array
    {
        $t = self::totals($studentId);
        $rand = Activities::random('task_12', $studentId);
        $p = fn ($n) => PriceRise::pct($n, false);
        $money = ['salary' => zeds($t['salary']), 'before' => zeds($t['before']), 'after' => zeds($t['after']), 'extra' => zeds($t['extra']),
            'left' => zeds($t['left']), 'left_before' => zeds($t['left_before'])];

        return [
            Activities::choice($rand, __('inflation.left.q_extra'),
                self::three($money['extra'], [zeds($t['extra'] * 2), zeds($t['salary'] * PriceRise::INDEX / 100)]),
                ['why' => __('inflation.left.why_extra', $money)]),
            Activities::choice($rand, __('inflation.left.q_left'),
                self::three($money['left'], [$money['left_before'], zeds($t['left_before'] + $t['extra'])]),
                ['why' => __('inflation.left.why_left', $money)]),
            Activities::choice($rand, __('inflation.left.q_share'),
                self::three($p($t['share']), [$p($t['share_before']), $p(round($t['share'] - PriceRise::INDEX, 1))]),
                ['why' => __('inflation.left.why_share', ['left' => $money['left'], 'salary' => $money['salary'], 'share' => $p($t['share']), 'share_before' => $p($t['share_before'])])]),
            Activities::choice($rand, __('inflation.left.q_do'),
                [__('inflation.left.a_spend_less'), __('inflation.left.a_nothing'), __('inflation.left.a_fund')],
                ['why' => __('inflation.left.why_do')]),
        ];
    }
}
