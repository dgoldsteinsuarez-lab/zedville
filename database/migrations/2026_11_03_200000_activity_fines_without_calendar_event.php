<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The plan of the month (Spending 4) is fined too when a student never
 * chooses it (Daniel, 2026-11-03: "Sí, también la multa"), and it isn't an
 * event of the class calendar: its fine has no calendar_event_id
 * (App\Services\MonthEndPenaltyService).
 */
return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('activity_fines')) {
            DB::statement('ALTER TABLE activity_fines MODIFY calendar_event_id BIGINT UNSIGNED NULL');
        }
    }

    public function down()
    {
        if (Schema::hasTable('activity_fines')) {
            DB::table('activity_fines')->whereNull('calendar_event_id')->delete();
            DB::statement('ALTER TABLE activity_fines MODIFY calendar_event_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
