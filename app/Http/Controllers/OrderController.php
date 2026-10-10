<?php

namespace App\Http\Controllers;
use App\Services\ParticipationService;
use App\Models\Transaction1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
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
                    'sid' => $user->sid,
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
                    'sid' => $user->sid,
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
}
