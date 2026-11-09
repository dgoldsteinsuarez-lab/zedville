<?php

use App\Models\Activity;
use App\Services\ShoppingList;
use Illuminate\Database\Migrations\Migration;

/**
 * A food shopping list's budget leaves room for what students choose (Daniel,
 * 2026-11-02, over budget on the Birthday Party: "no calculaste bien el tope,
 * creo que deberías poner un rango mayor, pero que se ajuste"): at least
 * ShoppingList::ROOM of the way from the cheapest basket to the dearest one.
 * Only budgets below that are raised; lists of named products keep theirs.
 */
return new class extends Migration {
    public function up()
    {
        foreach (Activity::whereNotNull('shopping_list')->get() as $activity) {
            $lines = ShoppingList::lines($activity);
            if (!$lines) {
                continue;
            }
            $budget = ShoppingList::budgetFor($lines, (float) $activity->budget);
            if ($budget > (float) $activity->budget) {
                $activity->update(['budget' => $budget]);
            }
        }
    }

    public function down()
    {
        // the budgets raised stay: they are what the lists cost
    }
};
