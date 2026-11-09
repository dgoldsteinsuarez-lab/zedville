<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-22: "I would like to be able to schedule autopilot for a
 * whole grade" (e.g. long school holidays). A whole-grade period is still
 * one row per student - everything that reads Autopilot (month-end
 * autopilot:apply, the penalty run, the report) works per student - and
 * `batch_id` ties those rows together so the admin screen shows and
 * cancels them as one. `grade_id` records which Grade it was set for.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('autopilot_periods', function (Blueprint $table) {
            $table->unsignedBigInteger('grade_id')->nullable()->after('class_id');
            $table->string('batch_id', 36)->nullable()->index()->after('grade_id');
        });
    }

    public function down()
    {
        Schema::table('autopilot_periods', function (Blueprint $table) {
            $table->dropIndex(['batch_id']);
            $table->dropColumn(['grade_id', 'batch_id']);
        });
    }
};
