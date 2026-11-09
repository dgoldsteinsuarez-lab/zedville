<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * A Spending Activity's shopping list (Daniel, 2026-10-31: "buscar 3 de
 * proteínas, carbos, etc. en una tienda, buscar unos pocos artículos
 * específicos" - instead of clicking until a minimum amount).
 *
 * A line asks for a number of products: from a supermarket food group
 * ({"qty":3,"group":"protein"}), from a few named products
 * ({"qty":1,"products":["Desk Lamp"]}) or from one shop
 * ({"qty":1,"store":"BeU - BeLuxury"}). The same product may count more than
 * once (Daniel, 2026-11-02: "si no hay muchas opciones, como las frutas, no
 * debe ser diferente, puede poner dos veces el mismo tipo de fruta"). Every
 * line is bought in one shop; a list over two shops is bought in two orders,
 * which add up (orders keep the activity and the products). Nothing off the
 * list, and the whole trip within the activity's budget.
 */
class ShoppingList
{
    public const GROUPS = ['protein', 'carbs', 'vegetables', 'fruit', 'dairy', 'fats'];

    /**
     * A food list's budget leaves room: at least this share of the way from
     * the cheapest basket to the dearest one (Daniel, 2026-11-02: "no
     * calculaste bien el tope, creo que deberías poner un rango mayor, pero
     * que se ajuste").
     */
    public const ROOM = 0.6;

    /** The prices a line can be bought at. */
    private static function prices(array $line): Collection
    {
        $query = isset($line['group'])
            ? DB::table('supermarkets')->where('food_group', $line['group'])
            : (isset($line['store'])
                ? DB::table('products')->where('type', $line['store'])
                : DB::table('products')->whereIn('product_name', $line['products'] ?? []));

        return $query->pluck('price')->map(fn ($price) => (float) $price)->filter(fn ($price) => $price > 0)->values();
    }

    /**
     * What the list costs bought all at the cheapest and all at the dearest:
     * ['min' => …, 'max' => …] - for $user, at the prices they pay (PriceRise).
     */
    public static function range(array $lines, ?User $user = null): array
    {
        $min = $max = 0.0;
        foreach ($lines as $line) {
            $prices = self::prices($line);
            $factor = PriceRise::lineFactor($user, $line);
            $min += $line['qty'] * (float) $prices->min() * $factor;
            $max += $line['qty'] * (float) $prices->max() * $factor;
        }

        return ['min' => round($min, 2), 'max' => round($max, 2)];
    }

    /** A budget with room for the list: ROOM of the way from its cheapest to its dearest basket, rounded up to Ƶ 5. */
    public static function suggestedBudget(array $lines): float
    {
        $range = self::range($lines);

        return (float) (ceil(round($range['min'] + self::ROOM * ($range['max'] - $range['min']), 2) / 5) * 5);
    }

    /**
     * The budget a list is saved with: what the admin wrote, raised to the
     * suggested one for a food list (its prices change with what's chosen;
     * a list of named products keeps the admin's).
     */
    public static function budgetFor(array $lines, float $budget): float
    {
        $food = collect($lines)->contains(fn ($line) => isset($line['group']));

        return $food ? max($budget, self::suggestedBudget($lines)) : $budget;
    }

    private static array $shopOfProducts = [];

    /** @return array<int, array{qty: int, group?: string, products?: array, store?: string, shop: string}> */
    public static function lines(Activity $activity): array
    {
        return collect($activity->shopping_list ?? [])
            ->map(fn ($line) => $line + ['shop' => self::shopOf($line)])
            ->values()->all();
    }

    public static function shopOf(array $line): string
    {
        if (isset($line['group'])) {
            return 'Supermarket';
        }
        if (isset($line['store'])) {
            return $line['store'];
        }
        $key = implode('|', $line['products'] ?? []);

        return self::$shopOfProducts[$key] ??= (string) DB::table('products')->whereIn('product_name', $line['products'] ?? [])->value('type');
    }

    /** $product: a supermarkets or products row; $catalog: which of the two. */
    public static function matches(array $line, object $product, string $catalog): bool
    {
        if (isset($line['group'])) {
            return $catalog === 'supermarkets' && ($product->food_group ?? null) === $line['group'];
        }
        if ($catalog !== 'products' || $product->type !== $line['shop']) {
            return false;
        }

        return isset($line['store']) || in_array($product->product_name, $line['products'] ?? [], true);
    }

    /**
     * Each product goes to the first line it fits that still needs one.
     *
     * @param  array  $done  line index => how many it already has
     * @return array{0: array, 1: array} [line index => count, the products that fit nowhere]
     */
    public static function assign(array $lines, array $items, array $done = []): array
    {
        $counts = [];
        foreach ($lines as $i => $line) {
            $counts[$i] = $done[$i] ?? 0;
        }
        $left = [];
        foreach ($items as [$product, $catalog]) {
            foreach ($lines as $i => $line) {
                if ($counts[$i] < $line['qty'] && self::matches($line, $product, $catalog)) {
                    $counts[$i]++;
                    continue 2;
                }
            }
            $left[] = $product;
        }
        ksort($counts);

        return [$counts, $left];
    }

    /** Since when the student's orders count: the class's assignment of this activity, or the month. */
    public static function since(Activity $activity, User $user): Carbon
    {
        $position = DB::table('mba_position')->where('activity_key', $activity->slot()[0])
            ->where('sid', $user->sid)->where('cid', $user->grade)->where('activity_id', $activity->id)->first();
        $start = $position->start_date ?? $position->created_at ?? null;

        return $start ? Carbon::parse($start)->startOfDay() : now()->startOfMonth();
    }

    /** What the student already bought for the activity: [[product, catalog], ...] and the money spent. */
    public static function bought(Activity $activity, User $user): array
    {
        $orders = DB::table('orders')->where('user_id', $user->id)->where('activity_id', $activity->id)
            ->where('created_at', '>=', self::since($activity, $user))->get(['id', 'total']);
        $items = [];
        foreach (DB::table('order_items')->whereIn('order_id', $orders->pluck('id'))->whereNotNull('product_id')->orderBy('id')->get() as $row) {
            $catalog = $row->catalog === 'supermarkets' ? 'supermarkets' : 'products';
            if ($product = DB::table($catalog)->find($row->product_id)) {
                // each one bought counts: two of the same fruit are two fruits
                for ($n = 0; $n < max(1, (int) $row->qty); $n++) {
                    $items[] = [$product, $catalog];
                }
            }
        }

        return [$items, round((float) $orders->sum('total'), 2)];
    }

    /** The list as it stands: every line with how many it has, the money spent and left. */
    public static function state(Activity $activity, User $user): array
    {
        $lines = self::lines($activity);
        [$items, $spent] = self::bought($activity, $user);
        [$counts] = self::assign($lines, $items);
        // in the Intermediate, the budget rises with the list's new prices, so it can still be bought (PriceRise)
        $budget = round((float) $activity->budget * PriceRise::listFactor($user, $lines), 2);

        foreach ($lines as $i => &$line) {
            $line['done'] = $counts[$i];
            $line['label'] = self::label($line);
        }

        return [
            'lines' => $lines,
            'budget' => $budget,
            'spent' => $spent,
            'left' => round(max(0, $budget - $spent), 2),
            // what the list costs, from the cheapest choices to the dearest
            'range' => self::range($lines, $user),
            'complete' => $lines && collect($lines)->every(fn ($l) => $l['done'] >= $l['qty']),
        ];
    }

    /**
     * What a shop page needs: the list with, for the lines bought there,
     * the products of the page that fit them, and each supermarket
     * product's food group (to say why something isn't on the list).
     */
    public static function forShop(Activity $activity, User $user, string $shop, Collection $products, string $catalog): array
    {
        $state = self::state($activity, $user);
        foreach ($state['lines'] as &$line) {
            $line['here'] = $line['shop'] === $shop;
            // the line for what is still missing: labels[n] says "n of it"
            $line['labels'] = collect(range(1, max(1, (int) $line['qty'])))->mapWithKeys(fn ($n) => [$n => self::label(['qty' => $n] + $line)])->all();
            $line['ids'] = $line['here']
                ? $products->filter(fn ($p) => self::matches($line, $p, $catalog))->pluck('id')->values()->all()
                : [];
            unset($line['products']);
        }
        $state['groups'] = $catalog === 'supermarkets'
            ? $products->mapWithKeys(fn ($p) => [$p->id => $p->food_group ? __('spending.list.group_names.' . $p->food_group) : null])->all()
            : [];
        $state['shop'] = $shop;

        return $state;
    }

    /**
     * Whether this cart may be paid for the activity (the server's check -
     * the page shows the same as the student shops). Null when it may, or
     * the reason in the student's language.
     *
     * @param  array  $cart  [[product, catalog, quantity], ...]
     */
    public static function checkoutError(Activity $activity, User $user, array $cart, float $total): ?string
    {
        $state = self::state($activity, $user);
        if ($state['complete']) {
            return __('spending.list.already_complete');
        }

        $shops = collect($cart)->map(fn ($c) => $c[1] === 'supermarkets' ? 'Supermarket' : $c[0]->type)->unique();
        if ($shops->count() !== 1) {
            return __('spending.list.one_shop');
        }
        $shop = $shops->first();

        // each one in the basket counts, the same product more than once too
        $units = [];
        foreach ($cart as [$product, $catalog, $quantity]) {
            for ($n = 0; $n < max(1, (int) $quantity); $n++) {
                $units[] = [$product, $catalog];
            }
        }

        $lines = $state['lines'];
        $here = array_filter($lines, fn ($line) => $line['shop'] === $shop);
        [$counts, $left] = self::assign($here, $units, array_map(fn ($l) => $l['done'], $here));
        if ($left) {
            // on the list, but that part already has enough - or not on it at all
            $full = collect($here)->first(fn ($line) => self::matches($line, $left[0], $cart[0][1]));

            return $full
                ? __('spending.list.line_full', ['line' => self::label($full)])
                : __('spending.list.not_on_list', ['item' => ct('products', $left[0]->product_name)]);
        }

        // what is still missing, not the whole line: "1 protein" when two are in
        $missing = collect($here)->filter(fn ($line, $i) => $counts[$i] < $line['qty'])
            ->map(fn ($line, $i) => self::label(['qty' => $line['qty'] - $counts[$i]] + $line));
        if ($missing->isNotEmpty()) {
            return __('spending.list.missing_here', ['items' => $missing->implode('; ')]);
        }

        if (round($state['spent'] + $total, 2) > round($state['budget'], 2)) {
            return __('spending.list.over_budget', ['spent' => \App\Support\Money::number($state['spent'] + $total), 'budget' => \App\Support\Money::number($state['budget'])]);
        }

        return null;
    }

    /** "3 proteins", "1 × Desk Lamp", "1 of these: …", "1 item from BeU - BeLuxury" - in the student's language. */
    public static function label(array $line): string
    {
        $qty = (int) $line['qty'];
        if (isset($line['group'])) {
            return trans_choice('spending.list.groups.' . $line['group'], $qty, ['count' => $qty]);
        }
        if (isset($line['store'])) {
            return trans_choice('spending.list.from_store', $qty, ['count' => $qty, 'store' => $line['store']]);
        }

        $names = array_map(fn ($name) => ct('products', $name), $line['products'] ?? []);
        if (count($names) === 1) {
            return __('spending.list.one_product', ['count' => $qty, 'name' => $names[0]]);
        }
        $last = array_pop($names);

        return __('spending.list.any_of', ['count' => $qty, 'options' => implode(', ', $names) . ' ' . __('spending.list.or') . ' ' . $last]);
    }
}
