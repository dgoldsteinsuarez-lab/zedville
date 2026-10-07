<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Save cart to session (per authenticated user)
     */
    public function save(Request $request)
    {
        $cart = $request->input('cart', []);
        $storeType = $request->input('store_type', 'default'); // get store type from frontend

        // Store cart in session per store type
        session(["cart_{$storeType}" => $cart]);

        return response()->json([
            'status' => true
        ]);
    }

    /**
     * Get cart from session
     */
    public function get(Request $request)
    {
        $storeType = $request->query('store_type', 'default'); // get store type from frontend

        return response()->json([
            'cart' => session("cart_{$storeType}", []) // return cart for this store type only
        ]);
    }

    /**
     * Clear cart after payment or reset
     */
    public function clear(Request $request)
    {
        $storeType = $request->input('store_type', 'default');

        // Clear only the cart for this store type
        session()->forget("cart_{$storeType}");

        return response()->json(['status' => true]);
    }
}
