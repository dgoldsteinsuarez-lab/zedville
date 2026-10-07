<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;
use App\Models\Grade;
use App\Models\Mascot;
use App\Models\SchoolDomain;
use App\Models\RoomPoster;
use App\Models\Product;
use App\Models\Store;
use App\Models\Supermarket;
use App\Models\Supermarketname;
use App\Models\Npo;

class AdminEducationController extends Controller
{
    public function index()
    {
        //return view('admin.education.index');
    }
    public function monthly_budget_activity()
    {
        $userId = Auth::id();
        $sid = session('selected_school');

        /*$position = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('id', 1)
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            })
            ->first();*/

        $positionQuery = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'mba');

        if ($sid) {
            // School selected
            $positionQuery->where('sid', $sid);
        } else {
            // No school selected
            $positionQuery->whereNull('sid');
        }

        $position = $positionQuery->first();

        // Show only classes of selected school
        $classes = DB::table('classes')
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            })
            ->get();
        // List
        $mbaList = DB::table('mba_position as mp')
            ->leftJoin('school_domains as sd', 'mp.sid', '=', 'sd.id')
            ->leftJoin('classes as c', function ($join) {
                $join->on('mp.cid', '=', 'c.id')
                    ->on('mp.sid', '=', 'c.sid');
            })
            ->where('mp.user_id', $userId)
            ->where('mp.activity_key', 'mba')
            ->when($sid, function ($q) use ($sid) {
                $q->where('mp.sid', $sid);
            }, function ($q) {
                $q->whereNull('mp.sid');
            })
            ->select(
                'mp.*',
                'sd.school_name',
                'sd.id as school_id', // <-- Fix here
                'c.name as class_name',
                'c.classCode'
            )
            ->orderByDesc('mp.id')
            ->get();

        return view('admin.education.monthly-budget-activity', compact('position', 'classes', 'mbaList'));
    }

    public function mba_position(Request $request)
    {
        $userId = Auth::id();
        $position = $request->input('position');
        $cid = $request->input('cid'); // Get selected class
        $id = $request->id;

        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;

        // ✅ Get sid from session or NULL
        $sid = session()->has('selected_school') ? session('selected_school') : null;
        /*DB::table('mba_position')->updateOrInsert(
            [
                'user_id' => $userId,
                //'id'      => 1,
                'sid'     => $sid,
                'cid'     => $cid,
            ],
            [
                'activity_key' => 'mba',
                'required'     => $required,
                'optional'     => $optional,
                'sid'          => $sid,
                'cid'          => $cid,
                'updated_at'   => now(),
            ]
        );*/
        /*
   |--------------------------------------------------------------------------
   | Check Duplicate
   |--------------------------------------------------------------------------
   | Don't allow same User + School + Class + Activity twice
   */
        $duplicate = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'mba')
            ->where('cid', $cid)
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            }, function ($q) {
                $q->whereNull('sid');
            })
            ->when($id, function ($q) use ($id) {
                // Ignore current record while updating
                $q->where('id', '!=', $id);
            })
            ->first();

        if ($duplicate) {
            return back()->with('error', 'This class already has an MBA Position.');
        }
        /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
        if ($id) {

            DB::table('mba_position')
                ->where('id', $id)
                ->update([
                    'sid' => $sid,
                    'cid' => $cid,
                    'required' => $required,
                    'optional' => $optional,
                    'updated_at' => now(),
                ]);

        } else {

            /*
            |--------------------------------------------------------------------------
            | Insert
            |--------------------------------------------------------------------------
            */

            DB::table('mba_position')->insert([
                'user_id' => $userId,
                'activity_key' => 'mba',
                'sid' => $sid,
                'cid' => $cid,
                'required' => $required,
                'optional' => $optional,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        //Insert Event under calander table
        $calendarEvent = DB::table('calendar_events')
            ->where('sid', $sid)
            ->where('classId', $cid)
            ->where('title', 'MBA')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->first();

        if ($calendarEvent) {

            DB::table('calendar_events')
                ->where('id', $calendarEvent->id)
                ->update([
                    'position' => $position,
                    'backgroundColor' => '#0053f9',
                    'borderColor' => '#0053f9',
                    'updated_at' => now(),
                ]);

        } else {

            DB::table('calendar_events')->insert([
                'sid' => $sid,
                'classId' => $cid, // Save class here too if column exists
                'title' => 'MBA',
                'description' => 'This is MBA',
                'position' => $position,
                'repeatActivity' => '0',
                'backgroundColor' => '#0053f9',
                'borderColor' => '#0053f9',
                'start' => now(),
                'end' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'MBA Position updated successfully!');
    }

    public function mba_position_delete($id)
    {
        $userId = Auth::id();

        $position = DB::table('mba_position')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$position) {
            return back()->with('error', 'Record not found.');
        }

        // Delete calendar event
        DB::table('calendar_events')
            ->where('sid', $position->sid)
            ->where('classId', $position->cid)
            ->where('title', 'MBA')
            ->delete();

        // Delete MBA position
        DB::table('mba_position')
            ->where('id', $id)
            ->delete();

        return back()->with('success', 'MBA Position deleted successfully.');
    }

    public function emergencyFundAccount()
    {
        $userId = Auth::id();
        $sid = session('selected_school');
        /*$position = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('id', 2)
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            })
            ->first();*/
        $positionQuery = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'emeaccount');

        if ($sid) {
            // School selected
            $positionQuery->where('sid', $sid);
        } else {
            // No school selected
            $positionQuery->whereNull('sid');
        }

        $position = $positionQuery->first();
        // Show only classes of selected school
        $classes = DB::table('classes')
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            })
            ->get();
        // List
        $mbaList = DB::table('mba_position as mp')
            ->leftJoin('school_domains as sd', 'mp.sid', '=', 'sd.id')
            ->leftJoin('classes as c', function ($join) {
                $join->on('mp.cid', '=', 'c.id')
                    ->on('mp.sid', '=', 'c.sid');
            })
            ->where('mp.user_id', $userId)
            ->where('mp.activity_key', 'emeaccount')
            ->when($sid, function ($q) use ($sid) {
                $q->where('mp.sid', $sid);
            }, function ($q) {
                $q->whereNull('mp.sid');
            })
            ->select(
                'mp.*',
                'sd.school_name',
                'sd.id as school_id', // <-- Fix here
                'c.name as class_name',
                'c.classCode'
            )
            ->orderByDesc('mp.id')
            ->get();
        return view('admin.education.emergency-fund-account-position', compact('position', 'classes', 'mbaList'));
    }

    public function emergencyFundPosition(Request $request)
    {
        $userId = Auth::id();
        $position = $request->input('position');
        $cid = $request->input('cid'); // Get selected class
        $id = $request->id;

        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;
        // ✅ Get sid from session or NULL
        $sid = session()->has('selected_school') ? session('selected_school') : null;
        /*DB::table('mba_position')->updateOrInsert(
            [
                'user_id' => $userId,
                'id'      => 2, 
                'sid'     => $sid,
                'cid'     => $cid,      // ✅ Emergency Fund
            ],
            [
                'activity_key' => 'emeaccount',
                'required'   => $required,
                'optional'   => $optional,
                'sid'          => $sid,
                'cid'          => $cid,
                'updated_at' => now(),
                'created_at' => now()
            ]
        );*/
        /*
  |--------------------------------------------------------------------------
  | Check Duplicate
  |--------------------------------------------------------------------------
  | Don't allow same User + School + Class + Activity twice
  */
        $duplicate = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'emeaccount')
            ->where('cid', $cid)
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            }, function ($q) {
                $q->whereNull('sid');
            })
            ->when($id, function ($q) use ($id) {
                // Ignore current record while updating
                $q->where('id', '!=', $id);
            })
            ->first();

        if ($duplicate) {
            return back()->with('error', 'This class already has an MBA Position.');
        }
        /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
        if ($id) {

            DB::table('mba_position')
                ->where('id', $id)
                ->update([
                    'sid' => $sid,
                    'cid' => $cid,
                    'required' => $required,
                    'optional' => $optional,
                    'updated_at' => now(),
                ]);

        } else {

            /*
            |--------------------------------------------------------------------------
            | Insert
            |--------------------------------------------------------------------------
            */

            DB::table('mba_position')->insert([
                'user_id' => $userId,
                'activity_key' => 'emeaccount',
                'sid' => $sid,
                'cid' => $cid,
                'required' => $required,
                'optional' => $optional,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        //Insert Event under calander table
        $calendarEvent = DB::table('calendar_events')
            ->where('sid', $sid)
            ->where('classId', $cid)
            ->where('title', 'Emergency Fund')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->first();

        if ($calendarEvent) {

            DB::table('calendar_events')
                ->where('id', $calendarEvent->id)
                ->update([
                    'position' => $position,
                    'backgroundColor' => '#0053f9',
                    'borderColor' => '#0053f9',
                    'updated_at' => now(),
                ]);

        } else {

            DB::table('calendar_events')->insert([
                'sid' => $sid,
                'classId' => $cid, // Save class here too if column exists
                'title' => 'Emergency Fund',
                'description' => 'This is Emergency Fund',
                'position' => $position,
                'repeatActivity' => '0',
                'backgroundColor' => '#0053f9',
                'borderColor' => '#0053f9',
                'start' => now(),
                'end' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return back()->with('success', 'Emergency Fund Account Position updated successfully!');
    }

    public function poster(Request $request)
    {
        $sid = session('selected_school');

        $posters = DB::table('room_poster')->orderByDesc('id')->get();

        return view('admin.education.poster.index', compact('posters'));
    }

    public function storePoster(Request $request)
    {
        // Validate required fields
        $request->validate([
            'poster_name' => 'required|string|max:255',
            'poster_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $userId = Auth::id();
        // ✅ Get SID from session (NULL if not set)
        $sid = session()->has('selected_school') ? session('selected_school') : null;
        // Upload image
        $imageName = null;
        if ($request->hasFile('poster_image')) {
            $image = $request->file('poster_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/room_poster'), $imageName);
        }

        // Insert into database (NO MODEL)
        DB::table('room_poster')->insert([
            'user_id' => $userId,
            'sid' => $sid,
            'poster_name' => $request->poster_name,
            'poster_image' => $imageName,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Poster added successfully!');
    }
    public function updatePoster(Request $request, $id)
    {
        $poster = RoomPoster::find($id);

        // ✅ Get SID from session (NULL if not selected)
        $sid = session()->has('selected_school') ? session('selected_school') : null;

        // ✅ Update sid ONLY if session exists
        if ($sid !== null) {
            $poster->sid = $sid;
        }

        $poster->poster_name = $request->poster_name;

        if ($request->hasFile('poster_image')) {
            $image = $request->poster_image;
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/room_poster'), $filename);
            $poster->poster_image = $filename;
        }

        $poster->save();

        return back()->with('success', 'Poster updated successfully');
    }
    public function deletePoster(Request $request, $id)
    {
        if ($request->confirm_code != $request->delete_code) {
            return back()->with('error', 'Confirmation code does not match.');
        }
        $poster = RoomPoster::find($id);

        if (!$poster) {
            return back()->with('error', 'Poster not found');
        }

        // Delete image file
        $imagePath = public_path('uploads/room_poster/' . $poster->poster_image);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Delete DB record
        $poster->delete();

        return back()->with('success', 'Poster deleted successfully');
    }
    public function spandBudget()
    {
        // Get SID from session (null if not selected)
        $sid = session('selected_school');

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
        $sid = session('selected_school');

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
    public function cityMallStore()
    {
        return view('admin.education.city-mall-store');
    }
    /*public function storeList()
    {
        //return response()->json(Store::latest()->get());
        return response()->json(
        Store::orderBy('id', 'ASC')->get()
    );

    }*/
    public function storeList()
    {
        $sid = session('selected_school');

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
    public function supermarket()
    {
        //$stores = Store::all(); 
        //$stores = Supermarketname::orderBy('id', 'ASC')->get();
        $sid = session('selected_school');

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
        //return response()->json(Supermarket::latest()->get());
        $sid = session('selected_school');

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
    //Wants Item
    public function wantsIteam()
    {
        //$stores = Store::all(); 
        //$stores = Supermarketname::orderBy('id', 'ASC')->get();
        $sid = session('selected_school');

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

        $sid = session('selected_school');

        DB::beginTransaction();

        try {

            foreach ($request->products as $item) {

                // Skip empty rows safely
                if (empty($item['product_name'])) {
                    continue;
                }

                DB::table('penalty_wants_items')->insert([
                    'sid' => $sid,
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
        //return response()->json(Supermarket::latest()->get());
        /*return response()->json(
        DB::table('penalty_wants_items')
            ->orderBy('id', 'ASC')
            ->get()
    );*/
        $sid = session('selected_school');

        $items = DB::table('penalty_wants_items')->orderBy('id', 'ASC')->get();

        return response()->json($items);
    }
    public function wantsIteamUpdate(Request $request, $id)
    {

        // ✅ Validate only required fields
        /* $request->validate([
             'product_name' => 'required|string|max:255',
             'price'        => 'required|numeric|min:0',
         ]);

         // ✅ Update using Query Builder (no model)
         DB::table('penalty_wants_items')
             ->where('id', $id)
             ->update([
                 'item_name' => $request->product_name,
                 'price'        => $request->price,
                 'updated_at'   => now(),
             ]);

         return response()->json([
             'success' => true,
             'message' => 'Product updated successfully'
         ]);*/
        $sid = session('selected_school');

        $query = DB::table('penalty_wants_items')->where('id', $id);

        if ($sid) {
            $query->where('sid', $sid);
        }

        $query->update([
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
    public function npos(Request $request)
    {
        $query = Npo::query();
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $npos = $query->latest()->get();
        return view('admin.npos.index', compact('npos'));
    }
    public function add_npo(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category' => 'required'
        ]);

        $npo = new Npo();
        $npo->name = $request->name;
        $npo->category = $request->category;
        $npo->slug = Str::slug($request->name);
        $npo->content = $request->content;
        $npo->bank_name = $request->bank_name;
        $npo->account_number = $request->account_number;
        $npo->status = $request->status ?? 1;
        $npo->sort_order = $request->sort_order ?? 0;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/npos'), $filename);
            $npo->image = $filename;
        }

        $npo->save();

        return redirect()->back()->with('success', 'NPO Added Successfully');
    }
    public function edit_npo($id)
    {

        $npo = Npo::findOrFail($id);

        $npos = Npo::latest()->get();

        return view('admin.npos.edit', compact('npo', 'npos'));

    }
    public function update_npo(Request $request, $id)
    {

        $npo = Npo::findOrFail($id);

        $npo->name = $request->name;
        $npo->category = $request->category;
        $npo->slug = Str::slug($request->name);
        $npo->content = $request->content;
        $npo->bank_name = $request->bank_name;
        $npo->account_number = $request->account_number;
        $npo->status = $request->status;
        $npo->sort_order = $request->sort_order;

        if ($request->hasFile('image')) {

            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/npos'), $filename);

            $npo->image = $filename;

        }

        $npo->save();

        return redirect()->back()->with('success', 'NPO Updated Successfully');

    }
    public function delete_npo(Request $request, $id)
    {
        if ($request->confirm_code != $request->delete_code) {
            return back()->with('error', 'Confirmation code does not match.');
        }
        Npo::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'NPO Deleted');

    }
    public function high_budget_activity()
    {
        $userId = Auth::id();
        $sid = session('selected_school');

        /*$position = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('id', 3)
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            })
            ->first();*/
        $positionQuery = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'hba_position');

        if ($sid) {
            // School selected
            $positionQuery->where('sid', $sid);
        } else {
            // No school selected
            $positionQuery->whereNull('sid');
        }

        $position = $positionQuery->first();
        // Show only classes of selected school
        $classes = DB::table('classes')
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            })
            ->get();
        // List
        $mbaList = DB::table('mba_position as mp')
            ->leftJoin('school_domains as sd', 'mp.sid', '=', 'sd.id')
            ->leftJoin('classes as c', function ($join) {
                $join->on('mp.cid', '=', 'c.id')
                    ->on('mp.sid', '=', 'c.sid');
            })
            ->where('mp.user_id', $userId)
            ->where('mp.activity_key', 'hba_position')
            ->when($sid, function ($q) use ($sid) {
                $q->where('mp.sid', $sid);
            }, function ($q) {
                $q->whereNull('mp.sid');
            })
            ->select(
                'mp.*',
                'sd.school_name',
                'sd.id as school_id', // <-- Fix here
                'c.name as class_name',
                'c.classCode'
            )
            ->orderByDesc('mp.id')
            ->get();
        return view('admin.education.high-budget-activity', compact('position', 'classes', 'mbaList'));
    }
    public function hba_position(Request $request)
    {
        $userId = Auth::id();
        $position = $request->input('position');
        $cid = $request->input('cid'); // Get selected class
        $id = $request->id;
        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;

        // ✅ Get sid from session or NULL
        $sid = session()->has('selected_school') ? session('selected_school') : null;

        /*DB::table('mba_position')->updateOrInsert(
            [
                'user_id' => $userId,
                'id'      => 3,        // ✅ High Budget Activity
                'sid'     => $sid,
                'cid'     => $cid,
            ],
            [
                'activity_key' => 'hba_position',
                'required'   => $required,
                'optional'   => $optional,
                'sid'        => $sid,
                'cid'          => $cid,
                'updated_at' => now(),
                'created_at' => now()
            ]
        );*/
        /*
   |--------------------------------------------------------------------------
   | Check Duplicate
   |--------------------------------------------------------------------------
   | Don't allow same User + School + Class + Activity twice
   */
        $duplicate = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('activity_key', 'hba_position')
            ->where('cid', $cid)
            ->when($sid, function ($q) use ($sid) {
                $q->where('sid', $sid);
            }, function ($q) {
                $q->whereNull('sid');
            })
            ->when($id, function ($q) use ($id) {
                // Ignore current record while updating
                $q->where('id', '!=', $id);
            })
            ->first();

        if ($duplicate) {
            return back()->with('error', 'This class already has an MBA Position.');
        }
        /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
        if ($id) {

            DB::table('mba_position')
                ->where('id', $id)
                ->update([
                    'sid' => $sid,
                    'cid' => $cid,
                    'required' => $required,
                    'optional' => $optional,
                    'updated_at' => now(),
                ]);

        } else {

            /*
            |--------------------------------------------------------------------------
            | Insert
            |--------------------------------------------------------------------------
            */

            DB::table('mba_position')->insert([
                'user_id' => $userId,
                'activity_key' => 'hba_position',
                'sid' => $sid,
                'cid' => $cid,
                'required' => $required,
                'optional' => $optional,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $calendarEvent = DB::table('calendar_events')
            ->where('sid', $sid)
            ->where('classId', $cid)
            ->where('title', 'Spending budget activity')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->first();

        if ($calendarEvent) {

            DB::table('calendar_events')
                ->where('id', $calendarEvent->id)
                ->update([
                    'position' => $position,
                    'backgroundColor' => '#0053f9',
                    'borderColor' => '#0053f9',
                    'updated_at' => now(),
                ]);

        } else {

            DB::table('calendar_events')->insert([
                'sid' => $sid,
                'classId' => $cid, // Save class here too if column exists
                'title' => 'Spending budget activity',
                'description' => 'This is Spending budget activity',
                'position' => $position,
                'repeatActivity' => '0',
                'backgroundColor' => '#0053f9',
                'borderColor' => '#0053f9',
                'start' => now(),
                'end' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'High Budget Position updated successfully!');
    }
    public function low_budget_activity()
    {
        $userId = Auth::id();
        $sid = session('selected_school');

        $position = DB::table('mba_position')
            ->where('user_id', $userId)
            ->where('id', 4)
            ->first();

        return view('admin.education.low-budget-activity', compact('position'));
    }
    public function lba_position(Request $request)
    {
        $userId = Auth::id();
        $position = $request->input('position');

        $required = $position === 'required' ? 1 : 0;
        $optional = $position === 'optional' ? 1 : 0;

        // ✅ Get sid from session or NULL
        $sid = session()->has('sid') ? session('sid') : null;

        DB::table('mba_position')->updateOrInsert(
            [
                'user_id' => $userId,
                'id' => 4,        // ✅ Low Budget Activity
            ],
            [
                'activity_key' => 'lba_position',
                'required' => $required,
                'optional' => $optional,
                'updated_at' => now(),
                'created_at' => now()
            ]
        );
        $calendarEvent = DB::table('calendar_events')
            ->where('sid', $sid)
            ->where('title', 'Spending Low activity')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->first();

        if ($calendarEvent) {

            DB::table('calendar_events')
                ->where('id', $calendarEvent->id)
                ->update([
                    'position' => $position,
                    'backgroundColor' => '#0053f9',
                    'borderColor' => '#0053f9',
                    'updated_at' => now(),
                ]);

        } else {

            DB::table('calendar_events')->insert([
                'sid' => $sid,
                'title' => 'Spending Low activity',
                'description' => 'This is Low budget activity',
                'position' => $position,
                'repeatActivity' => '0',
                'backgroundColor' => '#0053f9',
                'borderColor' => '#0053f9',
                'start' => now(),
                'end' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Low Budget Position updated successfully!');
    }
}
