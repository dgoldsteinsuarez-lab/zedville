<?php

namespace App\Services;

use App\Http\Controllers\BankController;
use App\Models\ConsumerSurvey;
use App\Models\Mailbox;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\Hybrid;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The Intermediate's price rise (Daniel, 2026-11-04 and 08: "the price rise ...
 * should have a justification and be explained, including a chart"; "some above
 * 5%, and they should see their buying power"). In the level's second month the
 * Zedville Statistics Office publishes its price index: 5% up on average (INDEX,
 * like Spain in 2026), and the salary stays the same all year. Not everything
 * rises the same (GROUPS): a drought spoiled the harvest (fruit, vegetables,
 * olives) and gas got dearer (electricity, transport); internet has a
 * fixed-price contract and electronics get cheaper. Weighted by what a typical
 * student pays in a month, the groups make the 5%.
 *
 * The student pays the new prices everywhere: the bills, the consumer survey's
 * monthly shopping, the plan of the month, the Supermarket, the City Mall and
 * the Spending Activity shops, whose budgets rise with their lists. What is
 * stored keeps the old prices (the catalogues, the survey): the rise is laid on
 * when a price is shown or paid. The levels after the Intermediate keep the new
 * prices. A letter from the City Hall announces it (announce()) and the
 * Statistics Office's page shows what rose, why and the student's own basket.
 */
class PriceRise
{
    /** The Statistics Office's index: +5% on average. */
    public const INDEX = 5.0;

    /** The level's month the prices rise in. */
    public const MONTH = 2;

    /** Each group's rise in %, the biggest first (the chart's order). */
    public const GROUPS = [
        'oil' => 20,
        'electricity' => 15,
        'fruit_veg' => 12,
        'transport' => 8,
        'bread' => 6,
        'eating_out' => 6,
        'protein' => 5,
        'school' => 5,
        'dairy' => 4,
        'leisure' => 4,
        'rent' => 3,
        'water' => 3,
        'mall' => 2,
        'internet' => 0,
        'electronics' => -3,
    ];

    /** The Supermarket's food groups (supermarkets.food_group). */
    public const FOOD = ['fats' => 'oil', 'fruit' => 'fruit_veg', 'vegetables' => 'fruit_veg', 'carbs' => 'bread', 'protein' => 'protein', 'dairy' => 'dairy'];

    /** City Mall shops (products.category) apart from the rest, which go with the clothes ('mall'). */
    public const MALL = ['Electronics' => 'electronics'];

    /** The bills of the 7th (BankController::billAmounts). */
    public const BILLS = ['Utility - Electricity' => 'electricity', 'Utility - Water' => 'water', 'Internet' => 'internet', 'Rent' => 'rent', 'School Fees' => 'school'];

    /** The consumer survey's lines besides the groceries; the school lunch is a meal out. */
    public const SURVEY = ['lunch' => 'eating_out', 'transport' => 'transport', 'activities' => 'leisure', 'restaurants' => 'eating_out'];

    /** The plan of the month (Spending 4) is leisure. */
    public const PLAN = 'leisure';

    private static array $on = [];

    /** Whether the student pays the new prices: in the Intermediate from its second month, after it always. */
    public static function on(?User $user, ?Carbon $now = null): bool
    {
        if (!$user || !Level::saver($user)) {
            return false;
        }
        $now ??= now();

        return self::$on[$user->id . '|' . $now->format('Y-m')] ??= Level::order($user) > Level::INTERMEDIATE || self::since($user, $now) !== null;
    }

    /** The first day of the new prices in the student's Intermediate class: its second month. Null before it. */
    public static function since(?User $user, ?Carbon $now = null): ?Carbon
    {
        $class = $user && $user->grade ? SchoolClass::find($user->grade) : null;
        if (!$class || Level::order($user) !== Level::INTERMEDIATE) {
            return null;
        }
        $now ??= now();
        $from = IntermediateClassService::start($class, $now)->addMonthsNoOverflow(self::MONTH - 1);

        return $now->gte($from) ? $from : null;
    }

    /** For tests and long-running commands: forget what was looked up. */
    public static function flush(): void
    {
        self::$on = [];
    }

    /** "+12 %", "0 %", "−3 %" in the reader's language ("+12%" in English). */
    public static function pct(float $n, bool $sign = true): string
    {
        $text = \App\Services\MoneyFactory\Activities::percent(abs($n));

        return $n < 0 ? '−' . $text : ($sign && $n > 0 ? '+' . $text : $text);
    }

    /** What a price of the group is multiplied by; a group not in the table rises like the index. */
    public static function factor(?string $group): float
    {
        return 1 + (self::GROUPS[$group] ?? self::INDEX) / 100;
    }

    /** The new price: with the group's rise, to the cent. */
    public static function risen(float $price, ?string $group): float
    {
        return round($price * self::factor($group), 2);
    }

    /** A price as the student pays it: the new one when the new prices are on. */
    public static function price(?User $user, float $price, ?string $group): float
    {
        return self::on($user) ? self::risen($price, $group) : round($price, 2);
    }

    /** The group of a Supermarket product (catalog 'supermarkets') or a City Mall one ('products'). */
    public static function groupOf(object $product, string $catalog): ?string
    {
        if ($catalog === 'supermarkets') {
            return self::FOOD[$product->food_group ?? ''] ?? null;
        }

        return self::MALL[$product->category ?? ''] ?? 'mall';
    }

    /** A product's price for the student. */
    public static function productPrice(?User $user, object $product, string $catalog): float
    {
        return self::price($user, (float) $product->price, self::groupOf($product, $catalog));
    }

    /** A shop page's products with the student's prices (only in memory: the catalogue keeps the old ones). */
    public static function reprice(?User $user, iterable $products, string $catalog): void
    {
        if (!self::on($user)) {
            return;
        }
        foreach ($products as $product) {
            $product->price = self::productPrice($user, $product, $catalog);
        }
    }

    /**
     * A plan's description with its parts' prices ("the cinema every Saturday
     * (Ƶ 80)") as the student pays them: they still add up to the plan's cost.
     */
    public static function planText(?User $user, string $text): string
    {
        if (!self::on($user)) {
            return $text;
        }

        return preg_replace_callback('/\(Ƶ\s?([\d.,]+)\)/u',
            fn ($m) => '(' . zeds(self::risen((float) preg_replace('/\D/', '', $m[1]), self::PLAN)) . ')', $text);
    }

    /** A Spending Activity's shopping-list line: what its prices are multiplied by for the student (1 without the rise). */
    public static function lineFactor(?User $user, array $line): float
    {
        if (!self::on($user)) {
            return 1.0;
        }
        if (isset($line['group'])) {
            return self::factor(self::FOOD[$line['group']] ?? null);
        }
        $categories = isset($line['store'])
            ? DB::table('products')->where('type', $line['store'])->distinct()->pluck('category')
            : DB::table('products')->whereIn('product_name', $line['products'] ?? [])->distinct()->pluck('category');

        return (float) ($categories->map(fn ($category) => self::factor(self::MALL[$category] ?? 'mall'))->max() ?? self::factor('mall'));
    }

    /** A shopping list's budget rises with its dearest line, so the list can still be bought; it never goes down. */
    public static function listFactor(?User $user, array $lines): float
    {
        return array_reduce($lines, fn ($max, $line) => max($max, self::lineFactor($user, $line)), 1.0);
    }

    /**
     * A consumer survey's monthly shopping as it was and with the new prices:
     * [line => ['before' => …, 'after' => …, 'group' => …]], 'groceries' first,
     * and the groceries' items. The groceries rise item by item.
     *
     * @return array{lines: array, items: array}
     */
    public static function basket(?ConsumerSurvey $survey): array
    {
        if (!$survey || $survey->isSkipped()) {
            return ['lines' => [], 'items' => []];
        }

        $items = collect(is_array($survey->groceries) ? $survey->groceries : (json_decode((string) $survey->groceries, true) ?: []));
        $groups = DB::table('supermarkets')->whereIn('id', $items->pluck('id')->filter()->all())->pluck('food_group', 'id');
        $rows = $items->map(function ($item) use ($groups) {
            $group = self::FOOD[$groups[$item['id'] ?? 0] ?? ''] ?? null;
            $before = round((float) ($item['total_price'] ?? 0), 2);

            return ['name' => (string) ($item['name'] ?? ''), 'group' => $group, 'before' => $before, 'after' => self::risen($before, $group)];
        })->filter(fn ($row) => $row['before'] > 0)->values();

        $itemsBefore = (float) $rows->sum('before');
        $groceries = round((float) ($survey->basket_cost ?: $itemsBefore), 2);
        // the basket's total as the survey saved it, risen as its items did
        $after = $itemsBefore > 0 ? round($groceries * $rows->sum('after') / $itemsBefore, 2) : self::risen($groceries, null);

        $lines = $groceries > 0 ? ['groceries' => ['before' => $groceries, 'after' => $after, 'group' => null]] : [];
        foreach (self::SURVEY as $field => $group) {
            $before = round((float) $survey->{$field}, 2);
            if ($before > 0) {
                $lines[$field] = ['before' => $before, 'after' => self::risen($before, $group), 'group' => $group];
            }
        }

        return ['lines' => $lines, 'items' => $rows->all()];
    }

    /**
     * The month's bills as they were and with the new prices; electricity and
     * water change every month, so their middle amount.
     */
    public static function bills(): array
    {
        $bills = [];
        foreach (BankController::billAmounts() as $description => [$low, $high]) {
            $before = round(($low + $high) / 2, 2);
            $bills[$description] = ['before' => $before, 'after' => self::risen($before, self::BILLS[$description]), 'group' => self::BILLS[$description]];
        }

        return $bills;
    }

    /** What the student's bills and monthly shopping cost before and after: ['before', 'after', 'extra']. */
    public static function month(User $user): array
    {
        $lines = array_merge(array_values(self::bills()), array_values(self::basket(ConsumerSurvey::inEffect($user->id))['lines']));
        $before = round(array_sum(array_column($lines, 'before')), 2);
        $after = round(array_sum(array_column($lines, 'after')), 2);

        return ['before' => $before, 'after' => $after, 'extra' => round($after - $before, 2)];
    }

    /**
     * The City Hall's letter, once, the first time the student comes in the
     * month the prices rise (the scheduler, the dashboard or the bank).
     */
    public static function announce(User $user): void
    {
        $since = self::since($user);
        if (!$since) {
            return;
        }
        $class = SchoolClass::find($user->grade);
        $sent = Mailbox::where('student_id', $user->id)->where('template', 'price-rise')
            ->where('created_at', '>=', IntermediateClassService::start($class))->exists();
        if ($sent) {
            return;
        }

        $data = ['month' => $since->format('Y-m')] + self::month($user);
        Mailbox::create([
            'student_id' => $user->id,
            'sid' => $user->sid,
            'subject' => 'Prices in Zedville have risen',
            'content' => Hybrid::without(fn () => view('mailbox_templates.price-rise', $data + ['user' => $user])->render()),
            'type' => 'primary',
            'template' => 'price-rise',
            'template_data' => $data,
            'read' => 0,
        ]);
    }
}
