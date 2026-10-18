<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supermarketname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin CRUD for the "Wants" items catalogue banks_penalty() randomly
 * picks from when applying the month-end penalty. Extracted out of
 * AdminEducationController (planning.md Phase 3 #4) - fully
 * self-contained.
 */
class PenaltyWantsItemController extends Controller
{
    public function wantsIteam()
    {
        $stores = Supermarketname::orderBy('id', 'ASC')->get();

        return view('admin.supermarket.wants-iteams', compact('stores'));
    }

    public function wantsIteamStore(Request $request)
    {
        // ✅ Validate ONLY required fields
        $request->validate([
            'products' => 'required|array',
            'products.*.product_name' => 'required|string|max:255',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            foreach ($request->products as $item) {

                // Skip empty rows safely
                if (empty($item['product_name'])) {
                    continue;
                }

                // Wants Items are "Fix for all schools" (Admin Structure
                // Proposal doc §7.1, same as Supermarket/City Mall/NPOs) -
                // never tied to session('selected_school'); banks_penalty()
                // already reads this table without any sid filter.
                DB::table('penalty_wants_items')->insert([
                    'sid' => null,
                    'item_name' => $item['product_name'],
                    'price' => $item['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Products saved successfully'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while saving products',
                'error' => $e->getMessage(), // remove in production if needed
            ], 500);
        }
    }

    public function wantsIteamList()
    {
        $items = DB::table('penalty_wants_items')->orderBy('id', 'ASC')->get();

        return response()->json($items);
    }

    public function wantsIteamUpdate(Request $request, $id)
    {
        // Wants Items are global (sid null, see wantsIteamStore) - a
        // leftover session('selected_school') filter here meant editing any
        // existing item silently no-op'd whenever a school was selected,
        // since no row's sid ever matched it.
        DB::table('penalty_wants_items')->where('id', $id)->update([
            'item_name' => $request->product_name,
            'price' => $request->price,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function wantsIteamDelete(Request $request, $id)
    {
        if ($request->confirm_code != $request->delete_code) {
            return back()->with('error', 'Confirmation code does not match.');
        }
        $deleted = DB::table('penalty_wants_items')
            ->where('id', $id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
