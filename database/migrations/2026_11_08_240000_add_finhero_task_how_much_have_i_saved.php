<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FinHero Task 5, "How much have I saved?" (Daniel, 2026-11-04: in the
 * Intermediate "una tarea de reflexión sobre cuánto ha podido ahorrar, simple,
 * que supliría a la de Match"): its default, worth what the Match was.
 */
return new class extends Migration {
    public function up()
    {
        if (!DB::table('finhero_activity_defaults')->where('activity_key', 'task_5')->exists()) {
            DB::table('finhero_activity_defaults')->insert([
                'activity_key' => 'task_5', 'activity_name' => 'How much have I saved?', 'type' => 'task',
                'max_points' => 3, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('finhero_activity_defaults')->where('activity_key', 'task_5')->delete();
    }
};
