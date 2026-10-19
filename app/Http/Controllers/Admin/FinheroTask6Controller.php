<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinheroTask6BlockedItem;
use App\Models\SchoolClass;
use App\Models\Supermarket;
use Illuminate\Http\Request;

/**
 * Substitute Goods feature, FinHero Task 6 (2026-10-19). Lets an
 * admin/tutor mark which Supermarket catalog items are "temporarily
 * unavailable" for a Class - the student-facing mechanism this drives
 * lives in BasketController.
 */
class FinheroTask6Controller extends Controller
{
    public function index(Request $request)
    {
        $sid = session('selected_school');
        $classId = $request->classId ?: null;

        $classes = $sid ? SchoolClass::where('sid', $sid)->get() : collect();

        $items = collect();
        $blockedIds = collect();

        if ($sid && $classId) {
            $items = Supermarket::orderBy('type')->orderBy('product_name')->get();
            $blockedIds = FinheroTask6BlockedItem::where('sid', $sid)
                ->where('cid', $classId)
                ->pluck('supermarket_id');
        }

        return view('admin.finhero.task6-blocked-items', compact('classes', 'classId', 'items', 'blockedIds'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'classId' => 'required|exists:classes,id',
            'blocked' => 'nullable|array',
            'blocked.*' => 'integer|exists:supermarkets,id',
        ]);

        $sid = session('selected_school');
        $classId = $request->classId;
        $blocked = $request->blocked ?? [];

        FinheroTask6BlockedItem::where('sid', $sid)->where('cid', $classId)->delete();

        foreach ($blocked as $supermarketId) {
            FinheroTask6BlockedItem::create([
                'sid' => $sid,
                'cid' => $classId,
                'supermarket_id' => $supermarketId,
            ]);
        }

        return redirect()->back()->with('success', 'Blocked items updated.');
    }
}
