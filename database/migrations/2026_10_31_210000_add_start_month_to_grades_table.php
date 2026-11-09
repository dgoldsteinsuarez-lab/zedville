<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-31: "i should be able to choose the month that a grade
 * starts, because it can be independent from the school". The calendar month
 * (1-12) that is Month 1 of this Grade's questions; null follows the school's
 * (school_month_settings.start_month).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->unsignedTinyInteger('start_month')->nullable()->after('language_level');
        });
    }

    public function down()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('start_month');
        });
    }
};
