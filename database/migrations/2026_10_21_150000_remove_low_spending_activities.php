<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-10-21: "you can remove low spending activities from the
 * system." The 10 'low' rows were seeded earlier today
 * (2026_10_21_120000_add_type_and_requirements_to_activities_table.php) -
 * a new migration removes the data rather than rewriting that already-run
 * one. No mba_position/calendar_events rows referenced them in either
 * database at the time of removal (confirmed directly before writing
 * this), so there's nothing downstream to clean up alongside them.
 */
return new class extends Migration
{
    public function up()
    {
        DB::table('activities')->where('type', 'low')->delete();
    }

    public function down()
    {
        // Deliberately not re-seeded - this is a one-way removal per
        // Daniel's explicit instruction, not a toggle.
    }
};
