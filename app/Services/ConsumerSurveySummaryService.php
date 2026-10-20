<?php

namespace App\Services;

use App\Models\ConsumerSurvey;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The "get the student's latest consumer survey, then compute basket/
 * lunch/transport/activities/restaurants/total" block was duplicated
 * byte-for-byte across SupermarketController::market_list()/supermarket()
 * and SpendingActivitiesController::market_list()/supermarket()
 * (planning.md Phase 3 #2). Each caller still owns its own control flow
 * around this (whether to redirect if no survey exists yet, whether to
 * require an activity_id, which view/variables to pass) - this only
 * extracts the part that was identical everywhere.
 */
class ConsumerSurveySummaryService
{
    public static function forUser(int $userId): array
    {
        $survey = ConsumerSurvey::where('user_id', $userId)->orderBy('id', 'desc')->first();
        $exists = (bool) $survey;
        $diet = $survey->diet ?? null;

        $groceryItems = [];
        if ($survey && $survey->groceries) {
            $groceryItems = is_array($survey->groceries)
                ? $survey->groceries
                : json_decode($survey->groceries, true);
        }
        $itemNames = collect($groceryItems)->pluck('name')->implode(', ');

        $basketTotal = $lunch = $transport = $activities = $restaurants = $total = 0;

        if ($survey) {
            $basketTotal = $survey->basket_cost ?? 0;
            $lunch = $survey->lunch ?? 0;
            $transport = $survey->transport ?? 0;
            $activities = $survey->activities ?? 0;
            $restaurants = $survey->restaurants ?? 0;

            $total = $survey->total_cost
                ?? ($basketTotal + $lunch + $transport + $activities + $restaurants);
        }

        return compact(
            'survey',
            'exists',
            'diet',
            'groceryItems',
            'itemNames',
            'basketTotal',
            'lunch',
            'transport',
            'activities',
            'restaurants',
            'total'
        );
    }

    /**
     * City-wide (same school + grade) average basket cost per calendar
     * month - the same CPI concept a real economy tracks: the changing
     * cost of a fixed basket of goods over time. Grouped by the real
     * created_at year/month, not the non-chronological `survey_month`
     * label, matching MoodController::city_mood()'s cityMonths query for
     * mood_logs - same cohort filter, same shape, deliberately consistent
     * (bugs.md/planning.md Phase 7/8's deferred "aggregate basket" item,
     * 2026-10-20).
     */
    public static function cityBasketTrend(User $user): array
    {
        return DB::table('consumer_surveys')
            ->join('users', 'users.id', '=', 'consumer_surveys.user_id')
            ->selectRaw('
                YEAR(consumer_surveys.created_at) AS year,
                MONTH(consumer_surveys.created_at) AS month,
                ROUND(AVG(consumer_surveys.basket_cost), 2) AS avgBasketCost,
                COUNT(*) AS studentCount
            ')
            ->where('users.sid', $user->sid)
            ->where('users.grade', $user->grade)
            ->groupByRaw('YEAR(consumer_surveys.created_at), MONTH(consumer_surveys.created_at)')
            ->orderByRaw('YEAR(consumer_surveys.created_at) DESC, MONTH(consumer_surveys.created_at) DESC')
            ->get()
            ->map(fn ($r) => [
                'year' => (int) $r->year,
                'month' => (int) $r->month,
                'avgBasketCost' => (float) $r->avgBasketCost,
                'studentCount' => (int) $r->studentCount,
            ])
            ->values()
            ->toArray();
    }
}
