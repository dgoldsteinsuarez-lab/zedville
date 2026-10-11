<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin CRUD for the "Spend Budget" products catalogue. Extracted out
 * of AdminEducationController (planning.md Phase 3 #4) - fully
 * self-contained.
 */
class SpendBudgetProductController extends Controller
{
    public function spandBudget()
    {
        $stores = Store::orderBy('id', 'ASC')->get();

        return view('admin.education.spand-budget', compact('stores'));
    }

    public function productStore(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.product_name' => 'required|string',
            'products.*.type' => 'required|string',
            'products.*.category' => 'required|string',
            'products.*.goods_type' => 'required|string',
            'products.*.price' => 'required|numeric',
            'products.*.image' => 'nullable|image|max:2048',
        ]);
        // ✅ Get SID from session (null if not set)
        $sid = session('selected_school');

        DB::beginTransaction();

        try {
            foreach ($request->products as $index => $item) {

                $imageName = null;

                if ($request->hasFile("products.$index.image")) {
                    $image = $request->file("products.$index.image");
                    $imageName = time() . '_' . $index . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/products'), $imageName);
                }

                Product::create([
                    'sid' => $sid,
                    'product_name' => $item['product_name'],
                    'type' => $item['type'],
                    'category' => $item['category'],
                    'goods_type' => $item['goods_type'],
                    'price' => $item['price'],
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

    public function productList()
    {
        $products = Product::latest()->get();

        return response()->json($products);
    }

    public function productUpdate(Request $request, $id)
    {
        $sid = session('selected_school');
        $product = Product::findOrFail($id);

        $product->product_name = $request->product_name;
        $product->type = $request->type;
        $product->category = $request->category;
        $product->goods_type = $request->goods_type; // New field
        $product->price = $request->price;
        // ✅ Update SID only if session exists
        if ($sid) {
            $product->sid = $sid;
        }
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '_' . $id . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/products'), $name);
            $product->image = $name;
        }

        $product->save();

        return response()->json(['success' => true]);
    }

    public function productDelete(Request $request, $id)
    {
        if ($request->confirm_code != $request->delete_code) {
            return back()->with('error', 'Confirmation code does not match.');
        }
        Product::where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}
