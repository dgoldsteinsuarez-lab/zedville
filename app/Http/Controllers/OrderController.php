<?php

namespace App\Http\Controllers;
use App\Services\ParticipationService;
use App\Models\Transaction1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function placeOrderold(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:4',
            'cart' => 'required|array',
            'total' => 'required|numeric|min:1'
        ]);

        $user = Auth::user();

        // 1. Get bank account
        $bankAccount = DB::table('bank_accounts')
            ->where('student_id', $user->id)
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'message' => 'Bank account not found'
            ], 404);
        }

        // 2. PIN check (PLAIN comparison)
        if ($request->pin != $bankAccount->card_pin) {
            return response()->json([
                'message' => 'Invalid PIN'
            ], 422);
        }

        // 3. Balance check
        $latestTxn = Transaction1::where('user_id', $user->id)
            ->latest('id') // or latest('created_at') if you track timestamps
            ->first();
        $lastBalance = $latestTxn ? $latestTxn->balance : 0;
        /*if ($lastBalance < $request->total) {
            return response()->json([
                'message' => 'Insufficient balance'
            ], 422);
        }
        if ($lastBalance < $request->total) {
            return response()->json([
                'status'             => false,
                'message'            => 'Insufficient balance',
                'available_balance'  => round($lastBalance, 2),
                'required_amount'    => round($request->total, 2),
                'short_by'           => round($request->total - $lastBalance, 2),
            ], 422);
        }*/
        if ($lastBalance < $request->total) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient balance. Your available balance is '
                    . number_format($lastBalance, 2)
                    . ' Z. Required amount is '
                    . number_format($request->total, 2)
                    . ' Z.'
            ], 422);

        }
        /*if ($bankAccount->primary_savings_account_amount < $request->total) {
            return response()->json([
                'message' => 'Insufficient balance'
            ], 422);
        }*/

        DB::beginTransaction();

        try {
            // 4. Deduct balance
            /*DB::table('bank_accounts')
                ->where('id', $bankAccount->id)
                ->update([
                    'primary_savings_account_amount' => $bankAccount->primary_savings_account_amount - $request->total,
                    'updated_at' => now()
                ]);*/

            // 5. Create order
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $user->id,
                'total' => $request->total,
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $runningBalance = $latestTxn->balance;
            // 6. Order items
            foreach ($request->cart as $item) {
                $itemSubtotal = $item['price'] * $item['quantity'];

                $runningBalance -= $itemSubtotal;

                if ($runningBalance < 0) {
                    throw new \Exception('Insufficient balance');
                }
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'sid' => session()->get('sid'),
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'qty' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'type' => $item['type'],
                    'category' => $item['category'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Transaction1::create([
                    'user_id' => $user->id,
                    'sid' => session()->get('sid'),
                    'bank_account_id' => $bankAccount->id,
                    'transaction_date' => now(),
                    'description' => "Purchase: {$item['name']} (Order #{$orderId})",
                    'type' => 'debit',
                    'category' => $item['category'],
                    'amount' => $itemSubtotal,
                    'balance' => $runningBalance,
                    'is_penalty' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $lastBalance = $runningBalance;
            // 7. Clear cart session
            session()->forget('cart');

            DB::commit();
            $shopId = '1';
            app(ParticipationService::class)->award($user->id, 'shopping', $shopId);

            return response()->json([
                'status' => true,
                'message' => 'Payment successful',
                'order_id' => $orderId,
                'balance' => $lastBalance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Payment failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function placeOrder(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:4',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        // 1. Get bank account
        $bankAccount = DB::table('bank_accounts')
            ->where('student_id', $user->id)
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'status' => false,
                'message' => 'Bank account not found.'
            ], 404);
        }

        // 2. Strict PIN check
        if ((string) $request->pin !== (string) $bankAccount->card_pin) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid PIN.'
            ], 422);
        }

        // 3. Server-side price and total calculation from products catalogue
        $serverTotal = 0;
        $validatedCart = [];

        foreach ($request->cart as $item) {
            $product = DB::table('products')
                ->where('id', $item['id'])
                ->first();

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'One or more products are no longer available.'
                ], 422);
            }

            $quantity = (int) $item['quantity'];
            $price = (float) $product->price;
            $itemSubtotal = $price * $quantity;
            $serverTotal += $itemSubtotal;

            $validatedCart[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $itemSubtotal,
            ];
        }

        DB::beginTransaction();

        try {
            // 4. Balance check — under row lock, and inside the transaction,
            // so two concurrent orders can't both read the same stale
            // balance and both pass this check (see bugs.md #5).
            $lastBalance = \App\Services\BalanceService::lockedBalance($user->id);

            if ($lastBalance < $serverTotal) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient balance. Your available balance is '
                        . number_format($lastBalance, 2)
                        . ' Z. Required amount is '
                        . number_format($serverTotal, 2)
                        . ' Z.'
                ], 422);
            }

            // 5. Create order using server-calculated total
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $user->id,
                'total' => $serverTotal,
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $runningBalance = $lastBalance;

            foreach ($validatedCart as $cartItem) {
                $product = $cartItem['product'];
                $quantity = $cartItem['quantity'];
                $itemSubtotal = $cartItem['subtotal'];

                $runningBalance -= $itemSubtotal;

                if ($runningBalance < 0) {
                    throw new \Exception('Insufficient balance.');
                }

                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'sid' => session()->get('sid'),
                    'name' => $product->product_name,
                    'price' => $product->price,
                    'qty' => $quantity,
                    'subtotal' => $itemSubtotal,
                    'type' => $product->type,
                    'category' => $product->category ?? 'Wants',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Transaction1::create([
                    'user_id' => $user->id,
                    'sid' => session()->get('sid'),
                    'bank_account_id' => $bankAccount->id,
                    'transaction_date' => now(),
                    'description' => "Purchase: {$product->product_name} (Order #{$orderId})",
                    'type' => 'debit',
                    'category' => $product->category ?? 'Wants',
                    'amount' => $itemSubtotal,
                    'balance' => $runningBalance,
                    'is_penalty' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            session()->forget('cart');

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully.',
                'order_id' => $orderId,
                'balance' => $runningBalance,
                'total' => $serverTotal,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Unable to place order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function placeOrderActivityold(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:4',
            'cart' => 'required|array',
            'total' => 'required|numeric|min:1'
        ]);

        $user = Auth::user();

        // 1. Get bank account
        $bankAccount = DB::table('bank_accounts')
            ->where('student_id', $user->id)
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'message' => 'Bank account not found'
            ], 404);
        }

        // 2. PIN check (PLAIN comparison)
        if ($request->pin != $bankAccount->card_pin) {
            return response()->json([
                'message' => 'Invalid PIN'
            ], 422);
        }

        // 3. Balance check
        $latestTxn = Transaction1::where('user_id', $user->id)
            ->latest('id') // or latest('created_at') if you track timestamps
            ->first();
        $lastBalance = $latestTxn ? $latestTxn->balance : 0;
        /*if ($lastBalance < $request->total) {
            return response()->json([
                'message' => 'Insufficient balance'
            ], 422);
        }
        if ($lastBalance < $request->total) {
            return response()->json([
                'status'             => false,
                'message'            => 'Insufficient balance',
                'available_balance'  => round($lastBalance, 2),
                'required_amount'    => round($request->total, 2),
                'short_by'           => round($request->total - $lastBalance, 2),
            ], 422);
        }*/
        if ($lastBalance < $request->total) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient balance. Your available balance is '
                    . number_format($lastBalance, 2)
                    . ' Z. Required amount is '
                    . number_format($request->total, 2)
                    . ' Z.'
            ], 422);

        }
        /*if ($bankAccount->primary_savings_account_amount < $request->total) {
            return response()->json([
                'message' => 'Insufficient balance'
            ], 422);
        }*/

        DB::beginTransaction();

        try {
            // 4. Deduct balance
            /*DB::table('bank_accounts')
                ->where('id', $bankAccount->id)
                ->update([
                    'primary_savings_account_amount' => $bankAccount->primary_savings_account_amount - $request->total,
                    'updated_at' => now()
                ]);*/

            // 5. Create order
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $user->id,
                'total' => $request->total,
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $runningBalance = $latestTxn->balance;
            // 6. Order items
            foreach ($request->cart as $item) {
                $itemSubtotal = $item['price'] * $item['quantity'];

                $runningBalance -= $itemSubtotal;

                if ($runningBalance < 0) {
                    throw new \Exception('Insufficient balance');
                }
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'sid' => session()->get('sid'),
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'qty' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'type' => $item['type'],
                    //'category'  => $item['category'],
                    'category' => 'Wants',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Transaction1::create([
                    'user_id' => $user->id,
                    'sid' => session()->get('sid'),
                    'bank_account_id' => $bankAccount->id,
                    'transaction_date' => now(),
                    'description' => "Purchase: {$item['name']} (Order #{$orderId})",
                    'type' => 'debit',
                    //'category'         => $item['category'],
                    'category' => 'Wants',
                    'amount' => $itemSubtotal,
                    'balance' => $runningBalance,
                    'is_penalty' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $lastBalance = $runningBalance;
            // 7. Clear cart session
            session()->forget('cart');

            DB::commit();
            //$shopId = '1';
            //app(ParticipationService::class)->award($user->id, 'shopping', $shopId);
            return response()->json([
                'status' => true,
                'message' => 'Payment successful',
                'order_id' => $orderId,
                'balance' => $lastBalance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Payment failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function placeOrderActivity(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Validate request
        |--------------------------------------------------------------------------
        |
        | DO NOT accept "total" from the browser.
        | The server calculates the total from the products table.
        |
        */
        $request->validate([
            'pin' => 'required|digits:4',
            'cart' => 'required|array|min:1',

            'cart.*.id' => 'required|integer|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | 2. Get bank account
        |--------------------------------------------------------------------------
        */
        $bankAccount = DB::table('bank_accounts')
            ->where('student_id', $user->id)
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'status' => false,
                'message' => 'Bank account not found'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. PIN check
        |--------------------------------------------------------------------------
        */
        /*if ($request->pin != $bankAccount->card_pin) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid PIN'
            ], 422);
        }*/
        $requestPin = (string) $request->pin;
        $storedPin = (string) $bankAccount->card_pin;

        if ($requestPin !== $storedPin) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid PIN'
            ], 422);
        }
        /*
        |--------------------------------------------------------------------------
        | 4. Get latest balance
        |--------------------------------------------------------------------------
        */
        $latestTxn = Transaction1::where('user_id', $user->id)
            ->latest('id')
            ->first();

        $lastBalance = $latestTxn
            ? (float) $latestTxn->balance
            : 0;

        /*
        |--------------------------------------------------------------------------
        | 5. IMPORTANT SECURITY FIX
        |--------------------------------------------------------------------------
        |
        | Never trust:
        |   - item price
        |   - item name
        |   - item type
        |   - item category
        |   - request total
        |
        | The client only provides:
        |   - product ID
        |   - quantity
        |
        | Everything else comes from products table.
        |
        */

        $serverTotal = 0;
        $validatedCart = [];

        foreach ($request->cart as $item) {

            /*
            |--------------------------------------------------------------------------
            | Get authoritative product information from database
            |--------------------------------------------------------------------------
            */
            $product = DB::table('products')
                ->where('id', $item['id'])
                ->first();

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'One or more products are unavailable.'
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Quantity comes from user, but price does NOT.
            |--------------------------------------------------------------------------
            */
            $quantity = (int) $item['quantity'];

            /*
            |--------------------------------------------------------------------------
            | Price comes ONLY from products.price
            |--------------------------------------------------------------------------
            */
            $price = (float) $product->price;

            /*
            |--------------------------------------------------------------------------
            | Calculate item subtotal on server
            |--------------------------------------------------------------------------
            */
            $itemSubtotal = $price * $quantity;

            /*
            |--------------------------------------------------------------------------
            | Calculate complete order total on server
            |--------------------------------------------------------------------------
            */
            $serverTotal += $itemSubtotal;

            /*
            |--------------------------------------------------------------------------
            | Store trusted product information for later use
            |--------------------------------------------------------------------------
            */
            $validatedCart[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $itemSubtotal,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Make sure balance is sufficient
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Use $serverTotal, NOT $request->total.
        |
        */
        if ($lastBalance < $serverTotal) {

            return response()->json([
                'status' => false,
                'message' => 'Insufficient balance. Your available balance is '
                    . number_format($lastBalance, 2)
                    . ' Z. Required amount is '
                    . number_format($serverTotal, 2)
                    . ' Z.'
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Start transaction
        |--------------------------------------------------------------------------
        */
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | 8. Create order
            |--------------------------------------------------------------------------
            |
            | Store server-calculated total.
            |
            */
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $user->id,
                'total' => $serverTotal,
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | 9. Calculate running balance
            |--------------------------------------------------------------------------
            */
            $runningBalance = $lastBalance;

            /*
            |--------------------------------------------------------------------------
            | 10. Create order items
            |--------------------------------------------------------------------------
            */
            foreach ($validatedCart as $cartItem) {

                $product = $cartItem['product'];
                $quantity = $cartItem['quantity'];
                $itemSubtotal = $cartItem['subtotal'];

                /*
                |--------------------------------------------------------------------------
                | Deduct SERVER-CALCULATED amount
                |--------------------------------------------------------------------------
                */
                $runningBalance -= $itemSubtotal;

                if ($runningBalance < 0) {
                    throw new \Exception('Insufficient balance');
                }

                /*
                |--------------------------------------------------------------------------
                | Store product information from DATABASE
                |--------------------------------------------------------------------------
                |
                | NOT:
                |   $item['name']
                |   $item['price']
                |   $item['type']
                |
                */
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'sid' => session()->get('sid'),

                    'name' => $product->product_name,

                    'price' => $product->price,

                    'qty' => $quantity,

                    'subtotal' => $itemSubtotal,

                    'type' => $product->type,

                    // Keep your existing Activity behaviour
                    'category' => 'Wants',

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Record transaction
                |--------------------------------------------------------------------------
                */
                Transaction1::create([
                    'user_id' => $user->id,
                    'sid' => session()->get('sid'),
                    'bank_account_id' => $bankAccount->id,
                    'transaction_date' => now(),

                    'description' =>
                        "Purchase: {$product->product_name} (Order #{$orderId})",

                    'type' => 'debit',

                    'category' => 'Wants',

                    'amount' => $itemSubtotal,

                    'balance' => $runningBalance,

                    'is_penalty' => 0,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 11. Final balance
            |--------------------------------------------------------------------------
            */
            $lastBalance = $runningBalance;

            /*
            |--------------------------------------------------------------------------
            | 12. Clear cart
            |--------------------------------------------------------------------------
            */
            session()->forget('cart');

            /*
            |--------------------------------------------------------------------------
            | 13. Commit transaction
            |--------------------------------------------------------------------------
            */
            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | 14. Response
            |--------------------------------------------------------------------------
            */
            return response()->json([
                'status' => true,
                'message' => 'Payment successful',
                'order_id' => $orderId,
                'balance' => $lastBalance
            ]);

        } catch (\Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | Rollback if anything fails
            |--------------------------------------------------------------------------
            */
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Payment failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
