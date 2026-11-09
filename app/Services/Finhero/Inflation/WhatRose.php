<?php

namespace App\Services\Finhero\Inflation;

use App\Models\User;
use App\Services\MoneyFactory\Activities;
use App\Services\PriceRise;

/**
 * FinHero Task 9, what went up? (Intermediate, the price rise of its second
 * month; Daniel, 2026-11-08: "qué subió y cuánto"). The Statistics Office's
 * bar chart and four questions: what rose more than the 5% average, how much
 * one thing rose, what got cheaper and a new price worked out.
 */
class WhatRose extends Quiz
{
    public const BASES = [50, 80, 200];

    public static function stage(int $studentId): array
    {
        return [
            'intro' => ['month' => self::riseMonth(User::find($studentId)), 'index' => self::index()],
            'groups' => collect(PriceRise::GROUPS)->map(fn ($pct, $key) => ['key' => $key, 'pct' => $pct, 'name' => __('prices.groups.' . $key)])->values()->all(),
        ];
    }

    public static function questions(int $studentId): array
    {
        $rand = Activities::random('task_9', $studentId);
        $groups = PriceRise::GROUPS;
        $above = array_keys(array_filter($groups, fn ($pct) => $pct > PriceRise::INDEX));
        $below = array_keys(array_filter($groups, fn ($pct) => $pct < PriceRise::INDEX));
        $name = fn (string $key) => __('prices.groups.' . $key);
        // with its sign as an option (+12 %), without it in a sentence (it went up 12 %)
        $pct = fn (string $key) => PriceRise::pct($groups[$key]);
        $plain = fn (string $key) => PriceRise::pct(abs($groups[$key]), false);

        // more than the average: one above it, two below it
        $up = $above[$rand->between(0, count($above) - 1)];
        $others = $rand->shuffle($below);

        // how much one thing rose: one that rose, not as much as the average itself
        $asked = array_values(array_filter(array_keys($groups), fn ($k) => $groups[$k] > 0 && $groups[$k] !== (int) PriceRise::INDEX));
        $how = $asked[$rand->between(0, count($asked) - 1)];
        $otherPcts = array_values(array_unique(array_map(fn ($k) => PriceRise::pct($groups[$k]), $rand->shuffle($asked))));

        // a new price: one of the groups that rose, on a round price
        $base = self::BASES[$rand->between(0, count(self::BASES) - 1)];
        $rose = array_keys(array_filter($groups, fn ($p) => $p > 0));
        $group = $rose[$rand->between(0, count($rose) - 1)];
        $plus = round($base * $groups[$group] / 100, 2);

        return [
            Activities::choice($rand, __('inflation.rise.q_above', ['index' => self::index()]),
                self::three($name($up), array_map($name, $others)),
                ['why' => __('inflation.rise.why_above', ['name' => self::quoted($up), 'pct' => $plain($up), 'index' => self::index()])]),
            Activities::choice($rand, __('inflation.rise.q_how', ['name' => self::quoted($how)]),
                self::three($pct($how), array_merge([PriceRise::pct(PriceRise::INDEX)], $otherPcts)),
                ['why' => __('inflation.rise.why_how', ['name' => self::quoted($how), 'pct' => $plain($how)])]),
            Activities::choice($rand, __('inflation.rise.q_down'),
                self::three($name('electronics'), [$name('internet'), $name('mall')]),
                ['why' => __('inflation.rise.why_down', ['pct' => $plain('electronics')])]),
            Activities::choice($rand, __('inflation.rise.q_new', ['base' => zeds($base), 'pct' => $plain($group)]),
                self::three(zeds($base + $plus), [zeds($base + $groups[$group]), zeds($base - $plus), zeds($base * 2)]),
                ['why' => __('inflation.rise.why_new', ['pct' => $plain($group), 'base' => zeds($base), 'plus' => zeds($plus), 'new' => zeds($base + $plus)])]),
        ];
    }
}
