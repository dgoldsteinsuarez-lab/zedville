<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Zedville_FinHero_Point_Values_v1.pdf §3 registers Task 3 at 33 points
 * (11 questions: Part A's 12 + Part B's 21). Only 10 question definitions
 * were ever actually built in resources/views/finhero/task-3.blade.php's
 * buildQuestions() - the 11th was never added, and isn't invented here
 * (that's curriculum content, not a code decision). True achievable max
 * with the Approach System (3 pts x 10 questions) is 30, not 33. Corrects
 * the 2026-10-18_000002 migration's seed, which used the doc's number
 * before this gap was found.
 */
return new class extends Migration {
    public function up()
    {
        DB::table('finhero_activity_defaults')
            ->where('activity_key', 'task_3')
            ->update(['max_points' => 30, 'updated_at' => now()]);
    }

    public function down()
    {
        DB::table('finhero_activity_defaults')
            ->where('activity_key', 'task_3')
            ->update(['max_points' => 33, 'updated_at' => now()]);
    }
};
