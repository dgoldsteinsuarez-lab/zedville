<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FinHero Task 15, «Elige tu seguro médico» (proposal «El seguro médico», step
 * 12): the student works out the cases of the two health insurance plans and
 * chooses one (App\Services\Finhero\Insurance\ChoosePlan).
 */
return new class extends Migration {
    public function up()
    {
        if (!DB::table('finhero_activity_defaults')->where('activity_key', 'task_15')->exists()) {
            DB::table('finhero_activity_defaults')->insert([
                'activity_key' => 'task_15', 'activity_name' => 'Choose your health insurance', 'type' => 'task', 'max_points' => 3,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('finhero_activity_defaults')->where('activity_key', 'task_15')->delete();
    }
};
