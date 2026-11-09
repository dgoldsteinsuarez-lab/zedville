<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\EngagementActivityController;
use App\Models\ConsumerSurvey;
use App\Models\SubstituteGoodsPick;
use App\Models\Supermarket;
use App\Services\ActivityCompletionService;
use App\Services\ConsumerSurveySummaryService;
use App\Services\ParticipationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Substitute Goods activity (2026-10-19): lets a student swap one item in
 * their shopping basket for a substitute from the same diet catalog,
 * without redoing the full Consumer Profile survey. Also the mechanism
 * the auto-picked "which items are temporarily unavailable" exercise is
 * built on - swapping out the last auto-picked item earns an Engagement
 * point.
 *
 * Daniel, 2026-10-19: this counts toward the **Engagement** badge, not
 * FinHero - "no deberia ser en Finance Badge... deberia ser en
 * Engagement badge... no deberia llamarse Task 6."
 *
 * Daniel, 2026-10-23: it is an activity an admin assigns to a class
 * (Admin > Engagement > Substitute Goods). Items only go out of stock
 * while it runs for the student's class; swapping the last one completes
 * it (calendar/dashboard/badge) and earns the Engagement point. Swapping
 * any item stays possible at any time.
 */
class BasketController extends Controller
{
    private const PICKS_PER_MONTH = 3;

    /**
     * This student's currently-blocked item ids for this month. Auto-picks
     * up to 3 random items from the student's own current basket the
     * first time this is called in a given month, so the selection stays
     * stable afterward.
     */
    private function blockedItemIdsFor($user): array
    {
        if (!EngagementActivityController::substituteGoodsActiveFor($user)) {
            return [];
        }

        $now = now();

        $existing = SubstituteGoodsPick::where('student_id', $user->id)
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->pluck('supermarket_id');

        if ($existing->isNotEmpty()) {
            return $existing->all();
        }

        $summary = ConsumerSurveySummaryService::forUser($user->id);
        if (!$summary['exists'] || empty($summary['groceryItems'])) {
            return [];
        }

        $picked = collect($summary['groceryItems'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->shuffle()
            ->take(self::PICKS_PER_MONTH);

        foreach ($picked as $supermarketId) {
            SubstituteGoodsPick::create([
                'student_id' => $user->id,
                'supermarket_id' => $supermarketId,
                'month' => $now->month,
                'year' => $now->year,
            ]);
        }

        return $picked->all();
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
            'name' => \App\Support\ContentTranslations::get('products', $item['name']),
            'total_price' => (float) $item['total_price'],
            'blocked' => in_array((int) $item['id'], $blockedIds, true),
        ])->values();

        return response()->json([
            'exists' => true,
            'active' => EngagementActivityController::substituteGoodsActiveFor($user),
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
            ->get(['id', 'product_name', 'price'])
            ->each(fn ($item) => $item->setAttribute('label', \App\Support\ContentTranslations::get('products', $item->product_name)));

        return response()->json(['items' => $items]);
    }

    public function swap(Request $request)
    {
        $request->validate([
            'old_item_id' => 'required|integer',
            'new_item_id' => 'required|integer|different:old_item_id',
        ]);

        $user = Auth::user();
        $survey = ConsumerSurvey::inEffect($user->id);

        if (!$survey || !is_array($survey->groceries)) {
            return response()->json(['status' => false, 'message' => __('shop.server.no_basket')], 422);
        }

        $groceries = $survey->groceries;
        $oldIndex = collect($groceries)->search(fn ($item) => (int) $item['id'] === (int) $request->old_item_id);

        if ($oldIndex === false) {
            return response()->json(['status' => false, 'message' => __('shop.server.not_in_basket')], 422);
        }

        if (collect($groceries)->contains(fn ($item) => (int) $item['id'] === (int) $request->new_item_id)) {
            return response()->json(['status' => false, 'message' => __('shop.server.already_in_basket')], 422);
        }

        $blockedIds = $this->blockedItemIdsFor($user);
        if (in_array((int) $request->new_item_id, $blockedIds, true)) {
            return response()->json(['status' => false, 'message' => __('shop.server.also_unavailable')], 422);
        }

        $newProduct = Supermarket::where('id', $request->new_item_id)
            ->where('type', $survey->diet)
            ->first();

        if (!$newProduct) {
            return response()->json(['status' => false, 'message' => __('shop.server.not_in_diet')], 422);
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

        $engagementAwarded = false;
        $activityCompleted = false;
        if ($wasBlocked) {
            $remainingBlocked = collect($blockedIds)->diff([$oldItemId]);
            $stillHasBlocked = collect($survey->groceries)->contains(fn ($item) => $remainingBlocked->contains((int) $item['id']));

            if (!$stillHasBlocked) {
                $engagementAwarded = app(ParticipationService::class)->award($user->id, 'substitute_goods_swap');
                $activityCompleted = true;
                app(ActivityCompletionService::class)->complete(
                    $user->id,
                    EngagementActivityController::SUBSTITUTE_GOODS,
                    EngagementActivityController::SUBSTITUTE_GOODS_TITLE
                );
            }
        }

        return response()->json([
            'status' => true,
            'basket_cost' => $basketCost,
            'total_cost' => $totalCost,
            'engagement_point_awarded' => $engagementAwarded,
            'activity_completed' => $activityCompleted,
        ]);
    }
}
