<?php

namespace App\Services\Finhero\Inflation;

use App\Models\SchoolClass;
use App\Models\User;
use App\Services\IntermediateClassService;
use App\Services\PriceRise;

/**
 * What the price-rise Tasks share (FinHero Tasks 9-13, App\Services\PriceRise):
 * the month the prices rose and options that are never the same twice.
 */
abstract class Quiz
{
    /** The right option first, then the first others that differ from it and from each other: three in all. */
    protected static function three(string $right, array $others): array
    {
        $options = [$right];
        foreach ($others as $other) {
            if (!in_array($other, $options, true)) {
                $options[] = $other;
            }
            if (count($options) === 3) {
                break;
            }
        }

        return $options;
    }

    /** The month the student's prices rose (their class's second month in the Intermediate), as a name. */
    protected static function riseMonth(?User $user): string
    {
        $class = $user && $user->grade ? SchoolClass::find($user->grade) : null;
        $month = PriceRise::since($user)
            ?? ($class ? IntermediateClassService::start($class)->addMonthsNoOverflow(PriceRise::MONTH - 1) : now()->startOfMonth());

        return $month->copy()->locale(app()->getLocale())->isoFormat('MMMM');
    }

    protected static function index(): string
    {
        return PriceRise::pct(PriceRise::INDEX, false);
    }

    /** A group's name, between the language's quotation marks. */
    protected static function quoted(string $group): string
    {
        return __('inflation.quote', ['name' => __('prices.groups.' . $group)]);
    }
}
