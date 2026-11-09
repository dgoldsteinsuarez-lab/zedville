<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fines for Required activities not done in time (Daniel, 2026-11-03: "Put
 * the penalty for the students that did not do the activities in the
 * educational finance department" - a 50 Ƶ fine for each one, charged the
 * day after its deadline; App\Services\ActivityFineService).
 *
 * activity_fines: one row per student and calendar event fined, so nobody is
 * charged twice and an approved Autopilot can give it back.
 * activity_fine_settings: the amount (0 = no fines) and the day the fines
 * started - only deadlines from that day on are fined, never older ones.
 */
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('activity_fines')) {
            Schema::create('activity_fines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('calendar_event_id');
                $table->string('activity_title');
                $table->dateTime('run_start');
                $table->dateTime('run_end');
                $table->decimal('amount', 10, 2);
                $table->unsignedBigInteger('transaction_id')->nullable();
                $table->timestamps();

                $table->unique(['student_id', 'calendar_event_id']);
                $table->index('calendar_event_id');
            });
        }

        if (!Schema::hasTable('activity_fine_settings')) {
            Schema::create('activity_fine_settings', function (Blueprint $table) {
                $table->id();
                $table->decimal('amount', 10, 2)->default(50);
                $table->date('active_since');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
            DB::table('activity_fine_settings')->insert([
                'amount' => 50,
                'active_since' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('activity_fine_settings');
        Schema::dropIfExists('activity_fines');
    }
};
