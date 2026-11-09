<?php

namespace App\Services\MoneyFactory;

use App\Support\Money;

/**
 * Central bank for a day (advanced, 2026-11-02). The student runs the Central
 * Bank of Zedland for four years. The goal, like the European Central Bank's:
 * prices rising about 2 % a year, too high or too low equally unwanted. Prices
 * rising too fast: raise interest rates; on target: leave them; a crisis with
 * prices hardly rising: cut them (and buy bonds when they are already at
 * zero); the government asking for money to pay its bills: no (in the euro
 * area the EU Treaty forbids it, Article 123). The first three years come in
 * the student's own order; the government always asks last.
 */
class CentralBankDay
{
    /** Each year: inflation, unemployment and growth (%). */
    public const YEARS = [
        'hot' => [7.0, 4.0, 3.5],
        'calm' => [2.1, 6.0, 1.5],
        'crisis' => [0.4, 10.0, -2.0],
        'government' => [2.3, 6.0, 1.0],
    ];

    public static function years(int $studentId): array
    {
        return array_merge(Activities::random('mf_central_bank', $studentId)->shuffle(['hot', 'calm', 'crisis']), ['government']);
    }

    /** "7.0%", "7,0 %" (a space before % but in English), "+3.5%". */
    private static function percent(float $value, bool $sign = false, int $decimals = 1): string
    {
        return ($sign && $value > 0 ? '+' : ($value < 0 ? '−' : '')) . Money::number(abs($value), $decimals)
            . (app()->getLocale() === 'en' ? '%' : "\u{00A0}%");
    }

    public static function stage(int $studentId): array
    {
        return ['intro' => ['target' => self::percent(2, false, 0)]];
    }

    public static function questions(int $studentId): array
    {
        $rand = Activities::random('mf_central_bank:questions', $studentId);
        $options = [
            'hot' => ['raise', 'cut', 'buy_bonds'],
            'calm' => ['keep', 'raise_lot', 'cut_zero'],
            'crisis' => ['cut', 'raise', 'print_state'],
            'government' => ['say_no', 'print_all', 'print_half'],
        ];

        $questions = [];
        foreach (self::years($studentId) as $i => $year) {
            [$inflation, $unemployment, $growth] = self::YEARS[$year];
            $questions[] = Activities::choice($rand, __('money_tasks.central_bank.q', ['n' => $i + 1]),
                array_map(fn ($option) => __('money_tasks.central_bank.options.' . $option), $options[$year]), [
                    'lead' => __('money_tasks.central_bank.years.' . $year),
                    'facts' => [
                        [__('money_tasks.central_bank.inflation'), self::percent($inflation)],
                        [__('money_tasks.central_bank.unemployment'), self::percent($unemployment)],
                        [__('money_tasks.central_bank.growth'), self::percent($growth, true)],
                    ],
                    'why' => __('money_tasks.central_bank.why.' . $year),
                ]);
        }

        return $questions;
    }
}
