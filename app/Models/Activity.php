<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A Spending Activity (type: 'high' - 'low' removed entirely 2026-10-21,
 * Daniel: "remove low spending activities from the system") - a real,
 * admin-selectable catalog entry a class can be assigned, consumed by the
 * student-facing listing page (EFDController::high_spending_activities())
 * and the shop-destination pages (SpendingActivitiesController::
 * supermarket()/spending_tracker()).
 */
class Activity extends Model
{
    protected $fillable = [
        'activity_type_id', 'type', 'number', 'title', 'mission',
        'requirements', 'special', 'shop', 'destination',
        'min_zeds', 'max_zeds', 'min_items', 'max_items',
        'shopping_list', 'budget', 'translations', 'levels',
    ];

    /**
     * High Spending and Low Spending (Daniel, 2026-10-31: "el teléfono es High
     * Spending o un ordenador"): a class gets one of each - type => [its
     * mba_position key, its calendar title, what finishing it records].
     */
    public const SLOTS = [
        'high' => ['hba_position', 'Spending budget activity', 'high_budget_activity'],
        'low' => ['lba_position', 'Low spending activity', 'low_budget_activity'],
    ];

    /** @return array{0: string, 1: string, 2: string} */
    public function slot(): array
    {
        return self::SLOTS[$this->type] ?? self::SLOTS['high'];
    }

    /** shopping_list and budget: App\Services\ShoppingList (Daniel, 2026-10-31). */
    protected $casts = [
        'shopping_list' => 'array',
        'translations' => 'array',
        'budget' => 'float',
        'levels' => 'array', // the grade levels it is for, null for every level (App\Support\AdminLevel)
    ];

    /**
     * The title, mission or destination in the student's language: what an
     * admin wrote for a new activity (translations), else the shared texts
     * (content-translations/<lang>/activities.php).
     */
    public function text(string $field): ?string
    {
        return $this->translations[app()->getLocale()][$field] ?? ct('activities', $this->$field);
    }
}
