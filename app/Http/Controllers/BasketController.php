<?php

namespace App\Http\Controllers;

use App\Models\ConsumerSurvey;
use App\Models\FinheroTask6BlockedItem;
use App\Models\Supermarket;
use App\Services\ConsumerSurveySummaryService;
use App\Services\FinheroBadgeCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Substitute Goods feature (2026-10-19): lets a student swap one item in
 * their shopping basket for a substitute from the same diet catalog,
 * without redoing the full Consumer Profile survey. Also the mechanism
 * FinHero Task 6 is built on - swapping out the last item an admin/tutor
 * has marked "temporarily unavailable" for the student's Class completes
 * the task.
 */
class BasketController extends Controller
{
    /**
     * This Class's currently-blocked item ids - empty unless Task 6 is
     * active for it. Scoped by sid+cid, never by diet directly (a blocked
     * row's own `type` already says which diet it belongs to).
     */
    private function blockedItemIdsFor($user): array
    {
        if (!$user->sid || !$user->grade) {
            return [];
        }

        $taskActive = DB::table('finhero_activity_registry')
            ->where('activity_key', 'task_6')
            ->where('type', 'task')
            ->where('is_active', true)
            ->where(function ($q) use ($user) {
                $q->whereNull('cid')->orWhere('cid', $user->grade);
            })
            ->where(function ($q) use ($user) {
                $q->whereNull('sid')->orWhere('sid', $user->sid);
            })
            ->exists();

        if (!$taskActive) {
            return [];
        }

        return FinheroTask6BlockedItem::where('sid', $user->sid)
            ->where('cid', $user->grade)
            ->pluck('supermarket_id')
            ->all();
    }

    /** The student's current basket, each item flagged if it's currently blocked (Task 6). */
    public function state(Request $request)
    {
        $user = Auth::user();
        $summary = ConsumerSurveySummaryService::forUser($user->id);

        if (!$summary['exists']) {
            return response()->json(['exists' => false, 'items' => []]);
        }

        $blockedIds = $this->blockedItemIdsFor($user);

        $items = collect($summary['groceryItems'])->map(fn ($item) => [
            'id' => (int) $item['id'],
            'name' => $item['name'],
            'total_price' => (float) $item['total_price'],
            'blocked' => in_array((int) $item['id'], $blockedIds, true),
        ])->values();

        return response()->json([
            'exists' => true,
            'diet' => $summary['diet'],
            'basket_cost' => $summary['basketTotal'],
            'items' => $items,
        ]);
    }

    /** Same-diet items the student could swap into: not already in their basket, not currently blocked. */
    public function options(Request $request)
    {
        $user = Auth::user();
        $summary = ConsumerSurveySummaryService::forUser($user->id);

        if (!$summary['exists']) {
            return response()->json(['items' => []]);
        }

        $basketIds = collect($summary['groceryItems'])->pluck('id')->map(fn ($id) => (int) $id);
        $blockedIds = $this->blockedItemIdsFor($user);

        $items = Supermarket::where('type', $summary['diet'])
            ->whereNotIn('id', $basketIds)
            ->whereNotIn('id', $blockedIds)
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'price']);

        return response()->json(['items' => $items]);
    }

    public function swap(Request $request)
    {
        $request->validate([
            'old_item_id' => 'required|integer',
            'new_item_id' => 'required|integer|different:old_item_id',
        ]);

        $user = Auth::user();
        $survey = ConsumerSurvey::where('user_id', $user->id)->orderBy('id', 'desc')->first();

        if (!$survey || !is_array($survey->groceries)) {
            return response()->json(['status' => false, 'message' => 'No shopping basket found.'], 422);
        }

        $groceries = $survey->groceries;
        $oldIndex = collect($groceries)->search(fn ($item) => (int) $item['id'] === (int) $request->old_item_id);

        if ($oldIndex === false) {
            return response()->json(['status' => false, 'message' => 'That item is not in your basket.'], 422);
        }

        if (collect($groceries)->contains(fn ($item) => (int) $item['id'] === (int) $request->new_item_id)) {
            return response()->json(['status' => false, 'message' => 'That item is already in your basket.'], 422);
        }

        $blockedIds = $this->blockedItemIdsFor($user);
        if (in_array((int) $request->new_item_id, $blockedIds, true)) {
            return response()->json(['status' => false, 'message' => 'That item is currently unavailable too.'], 422);
        }

        $newProduct = Supermarket::where('id', $request->new_item_id)
            ->where('type', $survey->diet)
            ->first();

        if (!$newProduct) {
            return response()->json(['status' => false, 'message' => 'That item is not part of your diet catalog.'], 422);
        }

        $oldItemId = (int) $request->old_item_id;
        $wasBlocked = in_array($oldItemId, $blockedIds, true);

        $quantity = (float) ($groceries[$oldIndex]['quantity'] ?? 1);
        $unitPrice = (float) $newProduct->price;

        $groceries[$oldIndex] = [
            'id' => $newProduct->id,
            'name' => $newProduct->product_name,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => round($unitPrice * $quantity, 2),
        ];

        $basketCost = round(collect($groceries)->sum('total_price'), 2);
        $totalCost = round($basketCost + $survey->lunch + $survey->transport + $survey->activities + $survey->restaurants, 2);

        $survey->groceries = array_values($groceries);
        $survey->basket_cost = $basketCost;
        $survey->total_cost = $totalCost;
        $survey->save();

        $task6Completed = false;
        if ($wasBlocked) {
            $remainingBlocked = collect($blockedIds)->diff([$oldItemId]);
            $stillHasBlocked = collect($survey->groceries)->contains(fn ($item) => $remainingBlocked->contains((int) $item['id']));

            if (!$stillHasBlocked) {
                $task6Completed = $this->awardTask6Points($user);
            }
        }

        return response()->json([
            'status' => true,
            'basket_cost' => $basketCost,
            'total_cost' => $totalCost,
            'task6_completed' => $task6Completed,
        ]);
    }

    private function awardTask6Points($user): bool
    {
        $now = now();

        $alreadyScored = DB::table('finhero_student_points')
            ->where('student_id', $user->id)
            ->where('source_type', 'task')
            ->where('source_key', 'task_6')
            ->whereMonth('earned_at', $now->month)
            ->whereYear('earned_at', $now->year)
            ->exists();

        if ($alreadyScored) {
            return false;
        }

        $maxPoints = (int) (
            DB::table('finhero_activity_registry')
                ->where('activity_key', 'task_6')
                ->where('type', 'task')
                ->value('max_points')
            ?? \App\Models\FinheroActivityDefault::where('activity_key', 'task_6')->value('max_points')
            ?? 0
        );

        if ($maxPoints <= 0) {
            return false;
        }

        $calculator = app(FinheroBadgeCalculatorService::class);

        DB::table('finhero_student_points')->insert([
            'student_id' => $user->id,
            'month' => $now->month,
            'year' => $now->year,
            'academic_year' => $calculator->getAcademicYear($now->month, $now->year),
            'source_type' => 'task',
            'source_key' => 'task_6',
            'points_earned' => $maxPoints,
            'earned_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return true;
    }
}
