<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Zedville_FinHero_Point_Values_v1.pdf §3 "Registered Activities — Max
 * Points": the authoritative default point value per task. Task 3 is
 * seeded as one combined activity (33 = Part A's 12 + Part B's 21) per the
 * doc's own stated registration choice - the doc flags this as still open
 * pending confirmation (§6), so if that changes, update this row's default,
 * not the schema.
 *
 * task_5 doesn't have a built view yet (see ProjectArchitecture.md §14.4)
 * but its default is seeded now so the admin Point Defaults screen and the
 * view, whenever it's built, already agree on the same number.
 */
return new class extends Migration {
    public function up()
    {
        $defaults = [
            ['activity_key' => 'task_1', 'activity_name' => 'Classify the Salary', 'type' => 'task', 'max_points' => 3],
            ['activity_key' => 'task_2', 'activity_name' => 'Budget Calculations', 'type' => 'task', 'max_points' => 24],
            ['activity_key' => 'task_3', 'activity_name' => '50/30/20 Rule', 'type' => 'task', 'max_points' => 33],
            ['activity_key' => 'task_4', 'activity_name' => 'Budget Reality Check', 'type' => 'task', 'max_points' => 24],
            ['activity_key' => 'task_5', 'activity_name' => 'Match Needs and Wants', 'type' => 'task', 'max_points' => 3],
        ];

        foreach ($defaults as $default) {
            DB::table('finhero_activity_defaults')->updateOrInsert(
                ['activity_key' => $default['activity_key']],
                array_merge($default, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    public function down()
    {
        DB::table('finhero_activity_defaults')
            ->whereIn('activity_key', ['task_1', 'task_2', 'task_3', 'task_4', 'task_5'])
            ->delete();
    }
};
