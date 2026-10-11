<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supermarket;
use App\Models\Supermarketname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin CRUD for the Supermarket products catalogue (not the same
 * resource as SpendBudgetProductController's "products" table -
 * Supermarket is its own table/model). Extracted out of
 * AdminEducationController (planning.md Phase 3 #4) - fully
 * self-contained.
 */
class SupermarketProductController extends Controller
{
    public function supermarket()
    {
        $stores = Supermarketname::orderBy('id', 'ASC')->get();

        return view('admin.supermarket.supermarket', compact('stores'));
    }

    public function supermarketStore(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.product_name' => 'required|string',
            'products.*.type' => 'required|string',
            'products.*.category' => 'required|string',
            'products.*.price' => 'required|numeric',
            'products.*.quantity' => 'required|numeric',
            'products.*.image' => 'nullable|image|max:2048',
        ]);

        $sid = session('selected_school');

        DB::beginTransaction();

        try {
            foreach ($request->products as $index => $item) {

                $imageName = null;

                if ($request->hasFile("products.$index.image")) {
                    $image = $request->file("products.$index.image");
                    $imageName = time() . '_' . $index . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/supermarket'), $imageName);
                }

                Supermarket::create([
                    'sid' => $sid,
                    'product_name' => $item['product_name'],
                    'type' => $item['type'],
                    'category' => $item['category'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'image' => $imageName,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Products uploaded successfully'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while uploading products'
            ], 500);
        }
    }

    public function supermarketList()
    {
        $products = Supermarket::latest()->get();

        return response()->json($products);
    }

    public function supermarketUpdate(Request $request, $id)
    {
        $sid = session('selected_school');

        $product = Supermarket::findOrFail($id);

        $product->product_name = $request->product_name;
        $product->type = $request->type;
        $product->category = $request->category;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        if ($sid) {
            $product->sid = $sid;
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '_' . $id . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/supermarket'), $name);
            $product->image = $name;
        }

        $product->save();

        return response()->json(['success' => true]);
    }

    public function supermarketDelete(Request $request, $id)
    {
        if ($request->confirm_code != $request->delete_code) {
            return back()->with('error', 'Confirmation code does not match.');
        }
        Supermarket::where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}
