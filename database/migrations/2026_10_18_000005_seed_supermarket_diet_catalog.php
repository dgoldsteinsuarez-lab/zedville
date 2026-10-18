<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Real grocery catalog from Daniel's "Salary and Savings Computation.xlsx"
 * doc (2026-10-18), per diet. This is the data the consumer-profile survey
 * needs to be completable at all (bugs.md #19) - each diet needs 10+
 * selectable items plus a "Cooking Oil" item (auto-included by the
 * survey's own JS). Every (unit_price, quantity) pair here multiplies to
 * the doc's own stated "Monthly Cost" for that row - verified individually
 * while transcribing. The doc's separate basket-total summary figures
 * (130.00 / 158.851 / 115.614 / 165.197) do NOT equal the sum of all rows
 * below for any diet - that total appears to reflect a different, smaller
 * selection (e.g. a specific 10-item basket), not the full catalog. Not
 * reconciled here; the per-item prices are what matters for the catalog,
 * and the survey computes its own total from whichever items a student
 * actually picks.
 *
 * type matches the consumer-profile survey's diet radio values exactly
 * (lowercase - the API route lowercases both sides, so case doesn't
 * matter, but the word itself must).
 */
return new class extends Migration {
    public function up()
    {
        $diets = [
            'vegetarian' => [
                ['Vegetarian Hamburger', 5.00, 4],
                ['Eggs', 2.50, 5],
                ['Cereals', 3.80, 3],
                ['Mixed Vegetables (frozen)', 5.51, 3],
                ['Seasonal Vegetables', 5.51, 2.5],
                ['Rice', 2.20, 7],
                ['Lentils', 3.31, 2],
                ['Chickpeas', 3.31, 2],
                ['Spinach', 6.61, 1],
                ['Lettuce', 1.50, 3],
                ['Tomato', 3.31, 2],
                ['Mixed Nuts', 17.64, 0.8],
                ['Pasta', 3.31, 4],
                ['Yogurt', 3.00, 3],
                ['Cheese', 11.02, 1.5],
                ['Bread and Toasts', 2.50, 6],
                ['Potato', 2.20, 6],
                ['Mixed National Fruits', 4.41, 6],
                ['Mixed International Fruits', 7.72, 3],
                ['Milk', 1.06, 12],
                ['Cooking Oil', 6.00, 1.5],
            ],
            'vegan' => [
                ['Plant-based Hamburger Patties', 4.00, 6],
                ['Tofu', 4.40, 3],
                ['Cereals', 3.80, 2.5],
                ['Mixed Vegetables (frozen)', 5.51, 3.5],
                ['Seasonal Vegetables', 5.51, 3],
                ['Rice', 2.20, 9],
                ['Lentils', 3.31, 3],
                ['Chickpeas', 3.31, 2.5],
                ['Spinach', 6.61, 1.2],
                ['Lettuce', 1.50, 4],
                ['Tomatoes', 3.31, 2.5],
                ['Mixed Nuts', 17.64, 1.5],
                ['Pasta (eggfree)', 2.20, 4],
                ['Plant-based Yogurt', 3.17, 4],
                ['Plant-based Cheese', 8.80, 1.2],
                ['Bread and Toasts (vegan)', 2.00, 7],
                ['Potatoes', 2.20, 8],
                ['Mixed National Fruits', 4.41, 3.5],
                ['Mixed International Fruits', 7.72, 2],
                ['Plant-based Milk', 3.00, 18],
                ['Cooking Oil', 6.00, 1.5],
            ],
            'omnivore' => [
                ['Hamburger', 3.00, 5],
                ['Chicken', 7.72, 3.5],
                ['Beef', 7.70, 2],
                ['Fish', 13.23, 1.2],
                ['Seafood', 17.64, 0.8],
                ['Yogurt', 3.00, 2.5],
                ['Eggs', 2.50, 5],
                ['Rice', 2.20, 4.5],
                ['Frozen Vegetables', 5.51, 2],
                ['Seasonal Vegetables', 5.51, 1.8],
                ['Potatoes', 2.20, 3.5],
                ['Bread', 2.50, 4],
                ['Mixed Nuts', 17.64, 0.6],
                ['Mixed National Fruits', 4.41, 2.2],
                ['Mixed International Fruits', 7.72, 1.2],
                ['Cheese', 11.02, 1],
                ['Lentils', 3.31, 1.2],
                ['Pasta', 3.31, 2],
                ['Milk', 1.06, 12],
                ['Cereals', 3.80, 3],
                ['Cooking Oil', 6.00, 1.5],
            ],
            'pescatarian' => [
                ['Fish Burger', 5.00, 6],
                ['Fish', 13.23, 3.5],
                ['Seafood', 17.64, 1.8],
                ['Tofu', 4.40, 2.2],
                ['Eggs', 2.50, 6],
                ['Cereals', 3.80, 3],
                ['Mixed Vegetables (frozen)', 5.51, 3],
                ['Seasonal Vegetables', 5.51, 2.2],
                ['Rice', 2.20, 6.5],
                ['Lentils', 3.31, 1.8],
                ['Spinach', 6.61, 1],
                ['Mixed Nuts', 7.00, 1],
                ['Pasta', 3.31, 3.5],
                ['Yogurt', 3.00, 3.5],
                ['Cheese', 11.02, 1.3],
                ['Bread and Toasts', 2.50, 6],
                ['Potatoes', 2.20, 4.5],
                ['Mixed National Fruits', 4.41, 3],
                ['Mixed International Fruits', 7.72, 1.8],
                ['Milk', 3.53, 10],
                ['Cooking Oil', 6.00, 1.5],
            ],
        ];

        $now = now();

        foreach ($diets as $type => $items) {
            DB::table('supermarketname')->updateOrInsert(
                ['name' => ucfirst($type)],
                ['sid' => null, 'created_at' => $now, 'updated_at' => $now]
            );

            foreach ($items as [$name, $price, $qty]) {
                $exists = DB::table('supermarkets')
                    ->where('type', $type)
                    ->where('product_name', $name)
                    ->exists();

                if (!$exists) {
                    DB::table('supermarkets')->insert([
                        'sid' => null,
                        'product_name' => $name,
                        'type' => $type,
                        'category' => 'Needs',
                        'price' => $price,
                        'quantity' => $qty,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down()
    {
        DB::table('supermarkets')
            ->whereIn('type', ['vegetarian', 'vegan', 'omnivore', 'pescatarian'])
            ->delete();

        DB::table('supermarketname')
            ->whereIn('name', ['Vegetarian', 'Vegan', 'Omnivore', 'Pescatarian'])
            ->delete();
    }
};
