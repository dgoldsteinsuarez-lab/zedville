<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-21: "i need to be able to add start and due date in all
 * activities, including the tasks, library and so on." Same start_date/
 * end_date pattern already added to mba_position for Monthly Budget/
 * Emergency Fund/Spending Activity - column names kept consistent with
 * that convention; the admin form labels this one "Start Date"/"Due Date"
 * to match Daniel's own wording for a task-shaped activity.
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('finhero_activity_registry', 'start_date')) {
            Schema::table('finhero_activity_registry', function (Blueprint $table) {
                $table->date('start_date')->nullable()->after('is_active');
                $table->date('end_date')->nullable()->after('start_date');
            });
        }
    }

    public function down()
    {
        Schema::table('finhero_activity_registry', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
