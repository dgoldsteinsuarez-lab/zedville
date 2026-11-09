<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The Intermediate's price rise of its second month, as FinHero Tasks 9-13
 * (Daniel, 2026-11-08: "con actividades para ver qué subió y cuánto, el poder
 * adquisitivo y cuánto ahorra ahora"): what went up, my shopping before and
 * after, what my salary is worth, what is left to save and what my money is
 * worth (App\Services\Finhero\QuizTasks, App\Services\PriceRise).
 */
return new class extends Migration {
    private const TASKS = [
        'task_9' => 'What went up?',
        'task_10' => 'My shopping, before and after',
        'task_11' => 'What is my salary worth?',
        'task_12' => 'How much is left to save?',
        'task_13' => 'What is my money worth?',
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
