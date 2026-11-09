<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Compound interest for the Intermediate, as FinHero Tasks 6-8 (Daniel,
 * 2026-11-08: "simple ones, so that they can see how much all growth"; it's
 * financial education, not the Money Factory's circulation of money): the
 * snowball, who will have more, the rule of 72 (App\Services\Finhero\QuizTasks).
 */
return new class extends Migration {
    private const TASKS = [
        'task_6' => 'The snowball: compound interest',
        'task_7' => 'Who will have more?',
        'task_8' => 'The rule of 72',
    ];

    public function up()
    {
        foreach (self::TASKS as $key => $name) {
            if (!DB::table('finhero_activity_defaults')->where('activity_key', $key)->exists()) {
                DB::table('finhero_activity_defaults')->insert([
                    'activity_key' => $key, 'activity_name' => $name, 'type' => 'task', 'max_points' => 3,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        DB::table('finhero_activity_defaults')->whereIn('activity_key', array_keys(self::TASKS))->delete();
    }
};
