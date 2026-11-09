<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FinHero Task 14, move the line (Daniel, 2026-11-08: "qué gráficos macro se
 * utilizan? ... que sean ellos quienes decidan cómo se mueve la línea"): supply
 * and demand behind the Intermediate's price rise, the student moves the line
 * (App\Services\Finhero\Inflation\MoveTheLine).
 */
return new class extends Migration {
    public function up()
    {
        if (!DB::table('finhero_activity_defaults')->where('activity_key', 'task_14')->exists()) {
            DB::table('finhero_activity_defaults')->insert([
                'activity_key' => 'task_14', 'activity_name' => 'Move the line: supply and demand', 'type' => 'task', 'max_points' => 3,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('finhero_activity_defaults')->where('activity_key', 'task_14')->delete();
    }
};
