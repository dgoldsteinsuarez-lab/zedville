<?php

namespace App\Http\Controllers;
use App\Services\ParticipationService;
use App\Models\Transaction1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * [category, description] of the single bank line an order becomes. A
     * Spending Activity's line says which one, so the student sees what the
     * purchase was for - "Compra: fiesta de cumpleaños" (Daniel, 2026-11-02;
     * App\Support\OrderLine).
     */
    public static function statementLine(string $catalogTable, bool $forSpendingActivity, ?string $activityTitle = null): array
    {
        if ($forSpendingActivity) {
            return ['Wants', $activityTitle ? 'Spending Activity: ' . $activityTitle : 'Spending Activity'];
        }

        return $catalogTable === 'supermarkets' ? ['Needs', 'Supermarket'] : ['Wants', 'City Mall'];
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:4',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer',
            'cart.*.quantity' => 'required|integer|min:1',
            // City Mall/spending-activity shops and the Supermarket are two
            // entirely separate catalogs (`products` vs `supermarkets`
            // tables), with their own, independently auto-incrementing
            // `id` columns - a cart item's id alone doesn't say which one
            // it came from. Every page that checks out here must say so
            // explicitly; defaulting to 'products' previously let a
            // Supermarket cart item's id collide with an unrelated
            // products.id, silently charging for the wrong item entirely
            // (e.g. "Chicken" id=47 resolving to products.id=47,
            // "Competition Basketball" - see bugs.md, 2026-10-19).
            'catalog' => 'nullable|in:products,supermarkets',
            // Sent by the Spending Activity shops (spendingActivities/*):
            // the activity this shopping trip is for.
            'activity_id' => 'nullable|integer',
            // from the Intermediate, the City Mall and the spending activities take the
            // credit card too (App\Services\CreditCardService, partials/pay-with)
            'pay_with' => 'nullable|in:account,card',
        ]);
        $byCard = $request->input('pay_with') === 'card';

        $catalogTable = $request->input('catalog') === 'supermarkets' ? 'supermarkets' : 'products';

        $user = Auth::user();

        // 1. Get bank account
        $bankAccount = DB::table('bank_accounts')
            ->where('student_id', $user->id)
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'status' => false,
                'message' => __('shop.server.no_bank_account')
            ], 404);
        }

        // 2. Strict PIN check
        if ((string) $request->pin !== (string) $bankAccount->card_pin) {
            return response()->json([
                'status' => false,
                'message' => __('shop.server.invalid_pin')
            ], 422);
        }

        // 3. Server-side price and total calculation from products catalogue
        $serverTotal = 0;
        $validatedCart = [];

        foreach ($request->cart as $item) {
            $product = DB::table($catalogTable)
                ->where('id', $item['id'])
                ->first();

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => __('shop.server.product_unavailable')
                ], 422);
            }

            $quantity = (int) $item['quantity'];
            // in the Intermediate, the new prices the shop shows (App\Services\PriceRise)
            $price = \App\Services\PriceRise::productPrice($user, $product, $catalogTable);
            $itemSubtotal = $price * $quantity;
            $serverTotal += $itemSubtotal;

            $validatedCart[] = [
                'product' => $product,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $itemSubtotal,
            ];
        }

        // A Spending Activity with a shopping list (Daniel, 2026-10-31): only
        // what is on the list, one of each, within the budget - the same
        // check the shop page shows while the student shops.
        $activity = $request->filled('activity_id') ? \App\Models\Activity::find($request->activity_id) : null;
        $listMode = $activity && !empty($activity->shopping_list);
        if ($listMode) {
            $error = \App\Services\ShoppingList::checkoutError($activity, $user,
                array_map(fn ($c) => [$c['product'], $catalogTable, $c['quantity']], $validatedCart), (float) $serverTotal);
            if ($error !== null) {
                return response()->json(['status' => false, 'message' => $error], 422);
            }
        }

        DB::beginTransaction();

        try {
            // 4. Balance check — under row lock, and inside the transaction,
            // so two concurrent orders can't both read the same stale
            // balance and both pass this check (see bugs.md #5).
            $lastBalance = \App\Services\LedgerService::lockedBalance($user->id);
            $card = $byCard ? \App\Services\CreditCardService::card($user->id) : null;
            if ($byCard && (!$card || \App\Services\CreditCardService::available($card) + 0.001 < $serverTotal)) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => __('flash.' . ($card ? 'card_limit' : 'card_none'))], 422);
            }

            if (!$byCard && $lastBalance < $serverTotal) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => __('shop.server.insufficient_balance', [
                        'balance' => number_format($lastBalance, 2),
                        'required' => number_format($serverTotal, 2),
                    ])
                ], 422);
            }

            // 5. Create order using server-calculated total
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $user->id,
                'total' => $serverTotal,
                'status' => 'paid',
                'activity_id' => $activity->id ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $runningBalance = $lastBalance;

            foreach ($validatedCart as $cartItem) {
                $product = $cartItem['product'];
                $quantity = $cartItem['quantity'];
                $itemSubtotal = $cartItem['subtotal'];

                if (!$byCard) {
                    $runningBalance -= $itemSubtotal;
                }

                if ($runningBalance < 0) {
                    throw new \Exception('Insufficient balance.');
                }

                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $product->id,
                    'catalog' => $catalogTable,
                    'sid' => $user->sid,
                    'name' => $product->product_name,
                    'price' => $cartItem['price'],
                    'qty' => $quantity,
                    'subtotal' => $itemSubtotal,
                    'type' => $product->type,
                    'category' => $product->category ?? 'Wants',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // One bank statement line per order, not one per item (Daniel,
            // 2026-10-22: "I don't want the bank statement to be full of
            // items"). The items stay in order_items. A Spending Activity is
            // always a Want - whatever is in the cart, even a donation or
            // groceries bought for it; a normal Supermarket trip is a Need.
            [$category, $label] = self::statementLine($catalogTable, $request->filled('activity_id'), $activity->title ?? null);
            if ($byCard) {
                // on the card: owed to the bank, paid with next month's statement - the current account is untouched
                \App\Services\CreditCardService::purchase($user, (float) $serverTotal, "{$label} (Order #{$orderId})", $orderId);
            } else {
                \App\Services\LedgerService::post(
                    $user->id,
                    'debit',
                    $category,
                    (float) $serverTotal,
                    "{$label} (Order #{$orderId})",
                    [
                        'sid' => $user->sid,
                        'bank_account_id' => $bankAccount->id,
                        'transaction_date' => now(),
                    ]
                );
            }

            session()->forget('cart');

            DB::commit();

            app(ParticipationService::class)->award($user->id, 'shopping', $catalogTable);

            // Checking out the Spending Activity assigned to the student's
            // class finishes it (blackboard + Engagement badge) - with a
            // shopping list, once the whole list is bought (it can take two
            // shops).
            $listState = $listMode ? \App\Services\ShoppingList::state($activity, $user) : null;
            if ($activity && (!$listMode || $listState['complete'])) {
                [$slotKey, $slotTitle, $slotType] = $activity->slot();
                $assigned = DB::table('mba_position')
                    ->where('activity_key', $slotKey)
                    ->where('sid', $user->sid)
                    ->where('cid', $user->grade)
                    ->where('activity_id', $request->activity_id)
                    ->exists();

                if ($assigned) {
                    app(\App\Services\ActivityCompletionService::class)
                        ->complete($user->id, $slotType, $slotTitle);
                }
            }

            return response()->json([
                'status' => true,
                'message' => __('shop.server.order_placed'),
                'order_id' => $orderId,
                'balance' => $runningBalance,
                'total' => $serverTotal,
                // written like the bank writes money, in the student's language
                'balance_text' => zeds($runningBalance),
                'total_text' => zeds($serverTotal),
                // paid with the credit card: what is left on it
                'paid_with' => $byCard ? 'card' : 'account',
                'card_available_text' => $byCard ? zeds(\App\Services\CreditCardService::available($card)) : null,
                // the list after this order: done, or the next shop to go to
                'list_complete' => $listState['complete'] ?? null,
                'list_message' => $listState === null ? null : ($listState['complete']
                    ? __('spending.list.already_complete')
                    : __('spending.list.next_shop', ['shop' => collect($listState['lines'])->first(fn ($l) => $l['done'] < $l['qty'])['shop']])),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => __('shop.server.order_failed'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
