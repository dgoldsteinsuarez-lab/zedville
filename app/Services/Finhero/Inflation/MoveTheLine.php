<?php

namespace App\Services\Finhero\Inflation;

use App\Services\MoneyFactory\Activities;

/**
 * FinHero Task 14, move the line (Intermediate; Daniel, 2026-11-08: "qué
 * gráficos macro se utilizan? ... que sean ellos quienes decidan cómo se mueve
 * la línea"). The macro chart behind a price rise, simply: supply (what firms
 * produce) and demand (what people want to buy); where they cross, the price
 * level - 100, as the Statistics Office's index. For four pieces of news the
 * student decides which line moves and which way, and the chart moves it and
 * shows the prices: Zedville's drought and dearer gas move supply to the left
 * (prices up, less produced: cost-push inflation, the Advance Economist's AS-AD
 * in simple), a good harvest to the right, a bonus for everyone moves demand to
 * the right (demand-pull), saving more to the left. Then what happened in
 * Zedville and what that kind of rise is called.
 */
class MoveTheLine extends Quiz
{
    /** The news and its right move: the options are always [supply left, supply right, demand left, demand right]. */
    public const NEWS = ['drought' => 0, 'harvest' => 1, 'save' => 2, 'bonus' => 3];

    public static function stage(int $studentId): array
    {
        return [
            // what the chart in each question says (finhero/_quiz)
            'chart' => [
                'title' => __('inflation.shift.chart'),
                'price' => __('inflation.shift.axis_price'),
                'amount' => __('inflation.shift.axis_amount'),
                'supply' => __('inflation.shift.supply'),
                'demand' => __('inflation.shift.demand'),
            ],
        ];
    }

    public static function questions(int $studentId): array
    {
        $rand = Activities::random('task_14', $studentId);
        $moves = [__('inflation.shift.a_supply_left'), __('inflation.shift.a_supply_right'), __('inflation.shift.a_demand_left'), __('inflation.shift.a_demand_right')];

        $questions = [];
        foreach ($rand->shuffle(array_keys(self::NEWS)) as $news) {
            $questions[] = Activities::fixed(__('inflation.shift.news_' . $news) . ' ' . __('inflation.shift.q_move'), $moves, self::NEWS[$news],
                ['why' => __('inflation.shift.why_' . $news), 'chart' => 'shift']);
        }
        $questions[] = Activities::choice($rand, __('inflation.shift.q_zedville'),
            [__('inflation.shift.a_up_less'), __('inflation.shift.a_up_more'), __('inflation.shift.a_down_less')],
            ['why' => __('inflation.shift.why_zedville')]);
        $questions[] = Activities::choice($rand, __('inflation.shift.q_name'),
            [__('inflation.shift.a_cost_push'), __('inflation.shift.a_demand_pull'), __('inflation.shift.a_compound')],
            ['why' => __('inflation.shift.why_name')]);

        return $questions;
    }
}
