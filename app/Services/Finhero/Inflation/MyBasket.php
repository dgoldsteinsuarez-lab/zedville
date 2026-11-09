<?php

namespace App\Services\Finhero\Inflation;

use App\Models\ConsumerSurvey;
use App\Services\MoneyFactory\Activities;
use App\Services\PriceRise;

/**
 * FinHero Task 10, my shopping, before and after (Intermediate; proposal «Mi
 * cesta antes y después»): the student's own monthly shopping from the
 * Consumer Profile Survey at the old prices and the new ones - or, without a
 * survey, a typical student's. What it costs now, how much more, by what % (not
 * always the city's 5%: each basket rises with what is in it) and in a year.
 */
class MyBasket extends Quiz
{
    /** A typical student's month (the surveys of 2026), for a student without a survey yet. */
    public const TYPICAL = ['groceries' => 135.0, 'lunch' => 25.0, 'transport' => 80.0, 'activities' => 75.0, 'restaurants' => 80.0];

    /** The lines [field => ['before', 'after']] and whether they are the student's own. */
    public static function lines(int $studentId): array
    {
        $lines = PriceRise::basket(ConsumerSurvey::inEffect($studentId))['lines'];
        if ($lines) {
            return [$lines, true];
        }
        $typical = [];
        foreach (self::TYPICAL as $field => $before) {
            $typical[$field] = ['before' => $before, 'after' => PriceRise::risen($before, PriceRise::SURVEY[$field] ?? null)];
        }

        return [$typical, false];
    }

    /** ['before', 'after', 'diff', 'pct'] of the whole month. */
    private static function totals(array $lines): array
    {
        $before = round(array_sum(array_column($lines, 'before')), 2);
        $after = round(array_sum(array_column($lines, 'after')), 2);

        return ['before' => $before, 'after' => $after, 'diff' => round($after - $before, 2), 'pct' => $before > 0 ? round(($after / $before - 1) * 100, 1) : 0.0];
    }

    public static function stage(int $studentId): array
    {
        [$lines, $own] = self::lines($studentId);
        $names = ['groceries' => 'surveys.wizard.groceries', 'lunch' => 'transactions.lines.School Lunch', 'transport' => 'transactions.lines.Transportation',
            'activities' => 'transactions.lines.Activities & Hobbies', 'restaurants' => 'transactions.lines.Eating Out'];

        return [
            'intro_key' => $own ? 'intro' : 'intro_typical',
            'rows' => collect($lines)->map(fn ($line, $field) => [
                'name' => __($names[$field]),
                'before' => zeds($line['before']),
                'after' => zeds($line['after']),
                'diff' => zeds($line['after'] - $line['before'], true),
            ])->values()->all(),
            'total' => (fn ($t) => ['before' => zeds($t['before']), 'after' => zeds($t['after']), 'diff' => zeds($t['diff'], true)])(self::totals($lines)),
        ];
    }

    public static function questions(int $studentId): array
    {
        [$lines] = self::lines($studentId);
        $t = self::totals($lines);
        $rand = Activities::random('task_10', $studentId);
        $money = ['before' => zeds($t['before']), 'after' => zeds($t['after']), 'diff' => zeds($t['diff'])];
        $pct = PriceRise::pct($t['pct'], false);
        $pcts = array_map(fn ($n) => PriceRise::pct($n, false), [PriceRise::INDEX, 20, 1, 12]);

        return [
            Activities::choice($rand, __('inflation.basket.q_now'),
                self::three($money['after'], [$money['before'], zeds($t['before'] * 1.2), zeds($t['after'] * 2)]),
                ['why' => __('inflation.basket.why_now', $money)]),
            Activities::choice($rand, __('inflation.basket.q_more'),
                self::three($money['diff'], [$money['before'], $money['after']]),
                ['why' => __('inflation.basket.why_more', $money)]),
            Activities::choice($rand, __('inflation.basket.q_pct'),
                self::three($pct, $pcts),
                ['why' => __('inflation.basket.why_pct', $money + ['pct' => $pct, 'index' => self::index()])]),
            Activities::choice($rand, __('inflation.basket.q_year'),
                self::three(zeds($t['diff'] * 12), [$money['diff'], zeds($t['diff'] * 6)]),
                ['why' => __('inflation.basket.why_year', $money + ['year' => zeds($t['diff'] * 12)])]),
        ];
    }
}
