<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin CRUD for City Mall stores. Extracted out of
 * AdminEducationController (planning.md Phase 3 #4) - fully
 * self-contained.
 */
class CityMallStoreController extends Controller
{
    public function cityMallStore()
    {
        return view('admin.education.city-mall-store');
    }

    public function storeList()
    {
        $stores = Store::orderBy('id', 'ASC')->get();

        return response()->json($stores);
    }

    public function CreatecityMallStore(Request $request)
    {
        $request->validate([
            'stores' => 'required|array',
            'stores.*.store_name' => 'required|string|max:255',
            'stores.*.store_image' => 'nullable|image|max:2048',
        ]);
        // ✅ Get SID (null if not selected)
        $sid = session('selected_school');
        DB::beginTransaction();

        try {
            foreach ($request->stores as $index => $item) {

                $imageName = null;

                if ($request->hasFile("stores.$index.store_image")) {
                    $image = $request->file("stores.$index.store_image");
                    $imageName = time() . '_' . $index . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/stores'), $imageName);
                }

                Store::create([
                    'sid' => $sid,
                    'store_name' => $item['store_name'],
                    'store_image' => $imageName,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stores added successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeUpdate(Request $request, $id)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        $sid = session('selected_school');

        $store = Store::findOrFail($id);

        $store->store_name = $request->store_name;

        if ($sid) {
            $store->sid = $sid;
        }

        if ($request->hasFile('store_image')) {

            // delete old image (optional but recommended)
            if ($store->store_image && file_exists(public_path('uploads/stores/' . $store->store_image))) {
                unlink(public_path('uploads/stores/' . $store->store_image));
            }

            $image = $request->file('store_image');
            $imageName = time() . '_' . $id . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/stores'), $imageName);

            $store->store_image = $imageName;
        }

        $store->save();

        return response()->json([
            'success' => true,
            'message' => 'Store updated successfully'
        ]);
    }

    public function storeDelete(Request $request, $id)
    {
        if ($request->confirm_code != $request->delete_code) {
            return back()->with('error', 'Confirmation code does not match.');
        }
        Store::where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}
