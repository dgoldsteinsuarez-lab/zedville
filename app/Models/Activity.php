<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A Spending Activity (type: 'high' or 'low') - a real, admin-selectable
 * catalog entry a class can be assigned, consumed by the student-facing
 * listing pages (EFDController::high_spending_activities()/
 * low_spending_activities()) and the shop-destination pages
 * (SpendingActivitiesController::supermarket()/spending_tracker()).
 */
class Activity extends Model
{
    protected $fillable = [
        'activity_type_id', 'type', 'number', 'title', 'mission',
        'requirements', 'special', 'shop', 'destination',
        'min_zeds', 'max_zeds', 'min_items', 'max_items',
    ];
}
