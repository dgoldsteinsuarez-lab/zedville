<?php

namespace App\Services\Finhero\CompoundInterest;

use App\Models\BankAccount;
use App\Services\MoneyFactory\Activities;
use App\Services\SavingsLedger;

/**
 * FinHero Task 17, «Tu dinero crece» (Intermediate, Required; proposal: "Con
 * su propia Money Market, calcula cuánto tendrá en 1, 5 y 10 años sin
 * tocarla, y lo compara con el interés simple y con empezar a ahorrar más
 * tarde"): the student's own Money Market balance - 1,000 Ƶ if it has less
 * than 100 - at their own rate, after 1, 5 and 10 years, next to simple
 * interest, and what starting five years later costs.
 */
class YourMoneyGrows
{
    public const SAMPLE = 1000;
    public const YEARS = [1, 5, 10];

    /** The student's Money Market balance in tens, or the sample; and whether it is theirs. */
    public static function amount(int $studentId): array
    {
        $account = BankAccount::where('student_id', $studentId)->first();
        $balance = $account ? SavingsLedger::balance($account, 'money_market') : 0;

        return $balance >= 100 ? [floor($balance / 10) * 10, true] : [(float) self::SAMPLE, false];
    }

    public static function stage(int $studentId): array
    {
        [$amount, $own] = self::amount($studentId);
        $rate = Snowball::rate($studentId);
        $top = Snowball::compound($amount, max(self::YEARS), $rate);

        return [
            'intro_key' => $own ? 'intro' : 'intro_sample',
            'intro' => ['amount' => zeds($amount), 'rate' => Activities::percent($rate * 100)],
            'rows' => array_map(fn ($years) => [
                'years' => $years,
                'compound' => zeds(Snowball::compound($amount, $years, $rate)),
                'simple' => zeds(Snowball::simple($amount, $years, $rate)),
                'compound_pct' => round(Snowball::compound($amount, $years, $rate) / $top * 100, 1),
                'simple_pct' => round(Snowball::simple($amount, $years, $rate) / $top * 100, 1),
            ], self::YEARS),
        ];
    }

    public static function questions(int $studentId): array
    {
        [$amount] = self::amount($studentId);
        $r = Snowball::rate($studentId);
        $c = fn ($y) => Snowball::compound($amount, $y, $r);
        $s = fn ($y) => Snowball::simple($amount, $y, $r);
        $rand = Activities::random('task_17', $studentId);

        return [
            Activities::choice($rand, __('compound.grows.q_five'), [zeds($c(5)), zeds($s(5)), zeds($c(1))],
                ['why' => __('compound.grows.why_five', ['amount' => zeds($amount), 'result' => zeds($c(5))])]),
            Activities::choice($rand, __('compound.grows.q_simple'), [zeds($s(10)), zeds($c(10)), zeds($s(5))],
                ['why' => __('compound.grows.why_simple', ['simple' => zeds($s(10)), 'compound' => zeds($c(10))])]),
            Activities::choice($rand, __('compound.grows.q_gap'), [zeds($c(10) - $s(10)), zeds($c(10) - $amount), zeds(0)],
                ['why' => __('compound.grows.why_gap', ['gap' => zeds($c(10) - $s(10))])]),
            Activities::choice($rand, __('compound.grows.q_later'), [zeds($c(5)), zeds($c(10)), zeds($s(10))],
                ['why' => __('compound.grows.why_later', ['later' => zeds($c(5)), 'now' => zeds($c(10)), 'lost' => zeds($c(10) - $c(5))])]),
        ];
    }
}
