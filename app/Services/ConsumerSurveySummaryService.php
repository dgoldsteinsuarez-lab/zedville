<?php

namespace App\Services;

use App\Models\ConsumerSurvey;

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
}
