<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-21: "classes have the same tasks, should be for grade,
 * the selection, by default, in case its not, there could be an option
 * extra for selecting only per class. One grade can have many classes."
 * A FinHero Task/Library activity is assigned to a whole Grade by default
 * (every class under it); `cid` becomes the explicit per-class override,
 * used only when an admin deliberately picks one specific class instead.
 * Exactly one of `grade_id` / `cid` is ever set per row - see
 * FinheroAdminController::addActivity()/updateActivity().
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('finhero_activity_registry', function (Blueprint $table) {
            $table->unsignedBigInteger('grade_id')->nullable()->after('cid');
        });
    }

    public function down()
    {
        Schema::table('finhero_activity_registry', function (Blueprint $table) {
            $table->dropColumn('grade_id');
        });
    }
};
