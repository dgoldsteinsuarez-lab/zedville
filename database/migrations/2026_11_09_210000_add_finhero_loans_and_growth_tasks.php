<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FinHero Tasks 16, «Compara dos préstamos», and 17, «Tu dinero crece»
 * (proposal, Intermediate activities; App\Services\Finhero\Credit\CompareLoans,
 * App\Services\Finhero\CompoundInterest\YourMoneyGrows).
 */
return new class extends Migration {
    private const TASKS = ['task_16' => 'Compare two loans', 'task_17' => 'Your money grows'];

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
