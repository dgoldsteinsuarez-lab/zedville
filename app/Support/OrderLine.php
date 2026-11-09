<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * A purchase on the bank statement (Daniel, 2026-11-02: "solo saldría una compra
 * con un nombre genérico y claro para que lo pueda poner en la actividad de Match,
 * por ejemplo, Compra Supermercado, pero si es para un Spending Activity, puedes
 * poner Compra Fiesta de Cumpleaños, para que lo ponga en deseos").
 *
 * Since 2026-10-22 an order is one ledger line: "Supermarket (Order #12)",
 * "City Mall (Order #14)" or "Spending Activity: Birthday Party Budget (Order #13)"
 * (App\Http\Controllers\OrderController). Older orders have one line per item
 * ("Purchase: Chicken (Order #4)"); the statement and the Needs, Wants & Savings
 * Match fold them into one line, named the same way. The ledger isn't changed.
 * App\Support\TransactionText says it to the student: "Compra en el supermercado",
 * "Compra: fiesta de cumpleaños".
 */
class OrderLine
{
    /** An item line of an order from before one line per order. */
    public const ITEM = '/^Purchase: .+ \(Order #(\d+)\)$/';

    /** The one line of an order: the Spending Activity it was for ('' if gone), or the shop. */
    public static function description(int $orderId, ?string $activity, bool $groceries): string
    {
        $what = match (true) {
            $activity === '' => 'Spending Activity',
            $activity !== null => 'Spending Activity: ' . $activity,
            default => $groceries ? 'Supermarket' : 'City Mall',
        };

        return "{$what} (Order #{$orderId})";
    }

    /**
     * Statement rows (oldest or newest first) with each older order's item lines
     * folded into one line where its first item was: the order's total, the date
     * of its first item and the balance after its last one. A folded line keeps
     * the order in `order_key` ("order-4"). Other rows are left as they are.
     */
    public static function fold(Collection $rows): Collection
    {
        $orderOf = fn ($row) => preg_match(self::ITEM, (string) $row->description, $m) ? (int) $m[1] : null;
        $items = $rows->filter(fn ($row) => $orderOf($row) !== null)->groupBy($orderOf);
        if ($items->isEmpty()) {
            return $rows;
        }

        $orders = self::orders($items->keys()->all());
        $folded = [];

        return $rows->map(function ($row) use ($orderOf, $items, $orders, &$folded) {
            $id = $orderOf($row);
            if ($id === null) {
                return $row;
            }
            if (isset($folded[$id])) {
                return null;
            }
            $folded[$id] = true;

            $lines = $items[$id]->sortBy('id');
            $order = $orders[$id] ?? null;
            // an order whose items don't say their shop: groceries if every item is a need
            $groceries = $order['groceries'] ?? null;
            $groceries ??= $lines->every(fn ($t) => \App\Http\Controllers\EFDController::budgetCategory($t->category, $t->description) === 'needs');

            $line = clone $row;
            $line->description = self::description($id, $order['activity'] ?? null, $groceries);
            $line->category = ($order['activity'] ?? null) === null && $groceries ? 'Needs' : 'Wants';
            $line->amount = round($lines->sum(fn ($t) => (float) $t->amount), 2);
            $line->balance = $lines->last()->balance;
            $line->transaction_date = $lines->first()->transaction_date;
            $line->order_key = 'order-' . $id;

            return $line;
        })->filter()->values();
    }

    /**
     * The orders' Spending Activity (its title, '' if it's gone, null if none) and
     * whether they were groceries (null when the items don't say their shop).
     */
    private static function orders(array $ids): array
    {
        $catalogs = DB::table('order_items')->whereIn('order_id', $ids)->get(['order_id', 'catalog'])->groupBy('order_id');

        return DB::table('orders')->leftJoin('activities', 'activities.id', '=', 'orders.activity_id')
            ->whereIn('orders.id', $ids)->get(['orders.id', 'orders.activity_id', 'activities.title'])
            ->mapWithKeys(function ($order) use ($catalogs) {
                $shops = isset($catalogs[$order->id]) ? $catalogs[$order->id]->pluck('catalog') : collect();

                return [(int) $order->id => [
                    'activity' => $order->activity_id ? (string) ($order->title ?? '') : null,
                    'groceries' => $shops->isEmpty() || $shops->contains(null) ? null : $shops->every(fn ($shop) => $shop === 'supermarkets'),
                ]];
            })->all();
    }
}
