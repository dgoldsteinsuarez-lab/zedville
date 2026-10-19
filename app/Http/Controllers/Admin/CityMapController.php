<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\CityMapSetting;
use App\Models\Grade;
use Illuminate\Http\Request;

/**
 * City Map, Phase 1 (2026-10-19). Super-Admin-only: upload/replace the
 * one shared city map hero image, and a read-only view of a Grade's
 * assigned addresses (support/debugging, not bulk editing - see
 * planning.md's City Map plan).
 */
class CityMapController extends Controller
{
    public function index()
    {
        $setting = CityMapSetting::current();
        $grades = Grade::orderBy('name')->get();

        return view('admin.city-map.index', compact('setting', 'grades'));
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);

        $file = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/city'), $filename);

        $setting = CityMapSetting::current();
        $setting->image = $filename;
        $setting->save();

        return redirect()->back()->with('success', 'City map image updated.');
    }

    public function addresses(Grade $grade)
    {
        $addresses = Address::with(['street', 'student:id,name,nickname'])
            ->where('grade_id', $grade->id)
            ->orderBy('street_id')
            ->orderBy('house_number')
            ->get();

        return view('admin.city-map.addresses', compact('grade', 'addresses'));
    }
}
