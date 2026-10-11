<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomPoster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Admin CRUD for Wellbeing Room posters. Extracted out of
 * AdminEducationController (planning.md Phase 3 #4) - fully
 * self-contained.
 */
class RoomPosterController extends Controller
{
    public function poster(Request $request)
    {
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
}
