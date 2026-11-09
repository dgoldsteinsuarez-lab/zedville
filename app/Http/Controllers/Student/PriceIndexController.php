<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ConsumerSurvey;
use App\Models\SchoolClass;
use App\Services\IntermediateClassService;
use App\Services\PriceRise;
use App\Support\Level;

/**
 * The Zedville Statistics Office: the Intermediate's price rise as the news
 * tells it (App\Services\PriceRise) - the index, what rose and why, with two
 * charts, and the student's own month before and now. The City Hall's letter
 * and the shops' notice link here.
 */
class PriceIndexController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $class = $user->grade ? SchoolClass::find($user->grade) : null;
        abort_unless($class && Level::order($user) === Level::INTERMEDIATE, 404);

        $riseMonth = IntermediateClassService::start($class)->addMonthsNoOverflow(PriceRise::MONTH - 1);
        $classMonth = (int) (IntermediateClassService::month($class) ?? 1);
        $basket = PriceRise::basket(ConsumerSurvey::inEffect($user->id));
        $lines = [];
        foreach (PriceRise::bills() as $description => $bill) {
            $name = __('transactions.lines.' . $description);
            // the utilities change every month: their middle amount
            $varies = in_array($description, ['Utility - Electricity', 'Utility - Water'], true);
            $lines[] = $bill + ['name' => $varies ? __('prices.on_average', ['item' => $name]) : $name];
        }
        $names = ['groceries' => 'surveys.wizard.groceries', 'lunch' => 'transactions.lines.School Lunch', 'transport' => 'transactions.lines.Transportation',
            'activities' => 'transactions.lines.Activities & Hobbies', 'restaurants' => 'transactions.lines.Eating Out'];
        foreach ($basket['lines'] as $field => $line) {
            $lines[] = $line + ['name' => __($names[$field])];
        }

        return view('prices.index', [
            'on' => PriceRise::on($user),
            'month' => $riseMonth->copy()->locale(app()->getLocale())->isoFormat('MMMM'),
            'groups' => collect(PriceRise::GROUPS)->map(fn ($pct, $key) => [
                'key' => $key, 'pct' => $pct, 'name' => __('prices.groups.' . $key), 'reason' => __('prices.reasons.' . $key),
            ])->values(),
            // the index from the level's first month to this one (at least the rise), month 1 = 100
            'series' => collect(range(1, max(PriceRise::MONTH, min(12, $classMonth))))
                ->map(fn ($n) => ['month' => $n, 'value' => $n >= PriceRise::MONTH ? 100 + PriceRise::INDEX : 100])->all(),
            'lines' => $lines,
            'hasSurvey' => !empty($basket['lines']),
            'before' => round(array_sum(array_column($lines, 'before')), 2),
            'after' => round(array_sum(array_column($lines, 'after')), 2),
        ]);
    }
}
