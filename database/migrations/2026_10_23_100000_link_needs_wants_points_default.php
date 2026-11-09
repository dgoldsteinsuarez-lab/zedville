<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-10-23: Needs, Wants & Savings Match was "doubled" in Admin -
 * a "Match Needs and Wants" row (task_5) in FinHero Point Defaults that
 * nothing read, next to the activity's own class-assignment page. The row
 * becomes the activity's real points setting (EFDController::storemba reads
 * it), under the key the activity already awards its points with.
 */
return new class extends Migration
{
    private const KEY = 'needs_wants_savings_match';

    public function up()
    {
        $exists = DB::table('finhero_activity_defaults')->where('activity_key', self::KEY)->exists();

        if (!$exists && DB::table('finhero_activity_defaults')->where('activity_key', 'task_5')->exists()) {
            DB::table('finhero_activity_defaults')->where('activity_key', 'task_5')->update([
                'activity_key' => self::KEY,
                'activity_name' => 'Needs, Wants & Savings Match',
                'updated_at' => now(),
            ]);
        } elseif (!$exists) {
            DB::table('finhero_activity_defaults')->insert([
                'activity_key' => self::KEY,
                'activity_name' => 'Needs, Wants & Savings Match',
                'type' => 'task',
                'max_points' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('finhero_activity_defaults')->where('activity_key', self::KEY)->update([
            'activity_key' => 'task_5',
            'activity_name' => 'Match Needs and Wants',
        ]);
    }
};
