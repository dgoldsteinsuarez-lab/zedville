<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-21: "i need to be able to add a start and end day of
 * each activity, for example, for Monthly Budget activity and emergency
 * fund account. That should be added in calendar of the student and
 * admin, tutor."
 *
 * `mba_position` is the one shared table behind all 4 "position" admin
 * screens (Mba/EmergencyFund/HighBudget/LowBudget - "Monthly Budget
 * Activity", "Emergency Fund Account", and the two "Spending Activity"
 * variants), keyed by `activity_key`. Each of those 4 controllers already
 * writes a matching `calendar_events` row - but today always with
 * `start = end = now()`, since no admin form has ever had a date field to
 * read from. These two columns are what that date range actually gets
 * stored against before being copied into `calendar_events` on save.
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('mba_position', 'start_date')) {
            Schema::table('mba_position', function (Blueprint $table) {
                $table->date('start_date')->nullable()->after('optional');
                $table->date('end_date')->nullable()->after('start_date');
            });
        }
    }

    public function down()
    {
        Schema::table('mba_position', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
