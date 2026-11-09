<?php

namespace App\Services\Finhero\Inflation;

use App\Models\BankAccount;
use App\Models\User;
use App\Services\MoneyFactory\Activities;
use App\Services\PriceRise;
use App\Services\SavingsLedger;
use App\Services\ZedDirecto;

/**
 * FinHero Task 13, what is my money worth? (Intermediate; proposal «¿Cuánto
 * vale tu dinero?»): the real interest of the Money Market Account - its rate
 * minus the price rise (the Banco de España's approximation) - with the
 * student's own balance, or 1,000 Ƶ while it has less than 100. Saving at 4%
 * with prices 5% up still loses value; at home it loses all 5%.
 */
class RealInterest extends Quiz
{
    public const SAMPLE = 1000;

    /** The amount the task uses: the student's Money Market balance, rounded down to tens, or the sample. */
    public static function amount(int $studentId): float
    {
        $account = BankAccount::where('student_id', $studentId)->first();
        $balance = $account ? SavingsLedger::balance($account, 'money_market') : 0;

        return $balance >= 100 ? floor($balance / 10) * 10 : self::SAMPLE;
    }

    /** ['amount', 'rate', 'index', 'real', 'have', 'cost'] - the rates in %. */
    private static function numbers(int $studentId): array
    {
        $amount = self::amount($studentId);
        // the student's own rate: 5 % from the Intermediate's third month (ZedDirecto)
        $rate = round(SavingsLedger::rate('money_market', User::find($studentId)) * 100, 2);

        return [
            'amount' => $amount, 'rate' => $rate, 'index' => PriceRise::INDEX, 'real' => round($rate - PriceRise::INDEX, 2),
            'have' => round($amount * (1 + $rate / 100), 2), 'cost' => round($amount * (1 + PriceRise::INDEX / 100), 2),
        ];
    }

    public static function stage(int $studentId): array
    {
        $n = self::numbers($studentId);
        $top = max($n['have'], $n['cost']);

        return [
            'intro' => ['amount' => zeds($n['amount']), 'account' => __('bank_ui.money_market'), 'rate' => PriceRise::pct($n['rate'], false), 'index' => self::index()],
            'bars' => [
                ['label' => __('inflation.real.have'), 'value' => zeds($n['have']), 'pct' => round($n['have'] / $top * 100, 1), 'kind' => 'have'],
                ['label' => __('inflation.real.cost', ['amount' => zeds($n['amount'])]), 'value' => zeds($n['cost']), 'pct' => round($n['cost'] / $top * 100, 1), 'kind' => 'cost'],
            ],
        ];
    }

    public static function questions(int $studentId): array
    {
        $n = self::numbers($studentId);
        $rand = Activities::random('task_13', $studentId);
        $p = fn ($x) => PriceRise::pct($x, false);
        $money = ['amount' => zeds($n['amount']), 'have' => zeds($n['have']), 'cost' => zeds($n['cost'])];
        $gap = round($n['have'] - $n['cost'], 2);

        if ($gap < 0) {
            $buy = [__('inflation.real.a_short', ['short' => zeds(-$gap)]), __('inflation.real.a_left', ['left' => zeds($n['have'] - $n['amount'])]), __('inflation.real.a_exact')];
            $why = __('inflation.real.why_buy_short', $money + ['short' => zeds(-$gap)]);
        } elseif ($gap > 0) {
            $buy = [__('inflation.real.a_left', ['left' => zeds($gap)]), __('inflation.real.a_short', ['short' => zeds($gap)]), __('inflation.real.a_exact')];
            $why = __('inflation.real.why_buy_left', $money + ['left' => zeds($gap)]);
        } else {
            $buy = [__('inflation.real.a_exact'), __('inflation.real.a_left', ['left' => zeds($n['have'] - $n['amount'])]), __('inflation.real.a_short', ['short' => zeds($n['amount'] / 100)])];
            $why = __('inflation.real.why_buy_exact', $money);
        }

        $questions = [
            Activities::choice($rand, __('inflation.real.q_real'),
                self::three($p($n['real']), [$p($n['rate']), $p($n['rate'] + $n['index']), $p(-$n['index'])]),
                ['why' => __('inflation.real.why_real', ['rate' => $p($n['rate']), 'index' => $p($n['index']), 'real' => $p($n['real'])])]),
            Activities::choice($rand, __('inflation.real.q_buy', $money), $buy, ['why' => $why]),
            Activities::choice($rand, __('inflation.real.q_home'),
                self::three($p(-$n['index']), [$p(0), $p($n['real']), $p($n['rate'])]),
                ['why' => __('inflation.real.why_home', ['zero' => $p(0), 'index' => $p($n['index']), 'home' => $p(-$n['index'])])]),
            Activities::choice($rand, __('inflation.real.q_protect'),
                [__('inflation.real.a_more_rate'), __('inflation.real.a_home'), __('inflation.real.a_low_rate', ['low' => $p(1)])],
                ['why' => __('inflation.real.why_protect')]),
        ];

        // once the second bank is open (the third month), the same sum there: comparing banks matters
        if (ZedDirecto::open(User::find($studentId))) {
            $zd = round(SavingsLedger::rate(ZedDirecto::SAVINGS) * 100, 2);
            $real = round($zd - $n['index'], 2);
            $questions[] = Activities::choice($rand, __('inflation.real.q_zd', ['rate' => $p($zd)]),
                self::three($p($real), [$p($zd), $p($zd + $n['index']), $p(-$real)]),
                ['why' => __('inflation.real.why_zd', ['rate' => $p($zd), 'index' => $p($n['index']), 'real' => $p($real)])]);
        }

        return $questions;
    }
}
