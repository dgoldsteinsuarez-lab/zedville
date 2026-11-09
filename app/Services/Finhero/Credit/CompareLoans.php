<?php

namespace App\Services\Finhero\Credit;

use App\Models\ConsumerSurvey;
use App\Models\User;
use App\Services\LoanService;
use App\Services\MoneyFactory\Activities;

/**
 * FinHero Task 16, «Compara dos préstamos» (Intermediate, Required; proposal:
 * "Con sus propios números, decide cuál cuesta menos en total"; theory
 * "Comparar créditos: por qué se compara con la TAE y no con la cuota"). For
 * half the student's salary, two offers: A, 12 months at 9 % APR; B, 24 months
 * at 7 %. B has the lower rate and the lower instalment, but lasts twice as
 * long: A costs less in all. With the same term, the lower APR is always
 * cheaper - that is why loans are compared by their APR and total cost, never
 * by the instalment. The page shows each offer's instalment; the student works
 * out the totals.
 */
class CompareLoans
{
    public const OFFERS = ['a' => ['months' => 12, 'tae' => 0.09], 'b' => ['months' => 24, 'tae' => 0.07]];

    /** Half the salary, in hundreds. */
    public static function amount(int $studentId): float
    {
        return max(500.0, round(ConsumerSurvey::salaryFor($studentId) * 0.5 / 100) * 100);
    }

    /** An offer's ['instalment', 'total', 'interest']: equal instalments at its APR. */
    public static function offer(float $amount, int $months, float $tae): array
    {
        $rate = (1 + $tae) ** (1 / 12) - 1;
        $instalment = round($amount * $rate / (1 - (1 + $rate) ** -$months), 2);
        $total = round($instalment * $months, 2);

        return ['instalment' => $instalment, 'total' => $total, 'interest' => round($total - $amount, 2)];
    }

    /** Both offers for the student, with their terms. */
    public static function offers(int $studentId): array
    {
        $amount = self::amount($studentId);

        return array_map(fn ($o) => $o + self::offer($amount, $o['months'], $o['tae']), self::OFFERS);
    }

    public static function stage(int $studentId): array
    {
        $offers = self::offers($studentId);

        return [
            'intro' => ['amount' => zeds(self::amount($studentId))],
            'offers' => array_map(fn ($o) => ['months' => $o['months'], 'tae' => Activities::percent($o['tae'] * 100), 'instalment' => zeds($o['instalment'])], $offers),
        ];
    }

    public static function questions(int $studentId): array
    {
        $amount = self::amount($studentId);
        ['a' => $a, 'b' => $b] = self::offers($studentId);
        $sameTerm = self::offer($amount, 12, self::OFFERS['b']['tae']);
        $rand = Activities::random('task_16', $studentId);
        $p = fn ($x) => Activities::percent($x * 100);
        $max = LoanService::maxLoad($studentId);
        $fits = LoanService::monthlyLoad($studentId) + $a['instalment'] <= $max;

        return [
            Activities::choice($rand, __('credit.compare.q_lower'), [__('credit.compare.offer_b'), __('credit.compare.offer_a'), __('credit.compare.same')],
                ['why' => __('credit.compare.why_lower', ['a' => zeds($a['instalment']), 'b' => zeds($b['instalment'])])]),
            Activities::choice($rand, __('credit.compare.q_interest_a'), [zeds($a['interest']), zeds($b['interest']), zeds($amount * self::OFFERS['a']['tae'])],
                ['why' => __('credit.compare.why_interest_a', ['instalment' => zeds($a['instalment']), 'months' => $a['months'], 'total' => zeds($a['total']), 'amount' => zeds($amount), 'interest' => zeds($a['interest'])])]),
            Activities::choice($rand, __('credit.compare.q_cheaper'), [__('credit.compare.offer_a'), __('credit.compare.offer_b'), __('credit.compare.same')],
                ['why' => __('credit.compare.why_cheaper', ['a' => zeds($a['interest']), 'b' => zeds($b['interest'])])]),
            Activities::choice($rand, __('credit.compare.q_same_term'), [__('credit.compare.offer_b'), __('credit.compare.offer_a'), __('credit.compare.same')],
                ['why' => __('credit.compare.why_same_term', ['b' => zeds($sameTerm['interest']), 'a' => zeds($a['interest']), 'tae_b' => $p(self::OFFERS['b']['tae']), 'tae_a' => $p(self::OFFERS['a']['tae'])])]),
            Activities::choice($rand, __('credit.compare.q_bank', ['max' => zeds($max)]),
                $fits ? [__('credit.compare.yes'), __('credit.compare.no')] : [__('credit.compare.no'), __('credit.compare.yes')],
                ['why' => __($fits ? 'credit.compare.why_yes' : 'credit.compare.why_no', ['instalment' => zeds($a['instalment']), 'max' => zeds($max)])]),
        ];
    }
}
