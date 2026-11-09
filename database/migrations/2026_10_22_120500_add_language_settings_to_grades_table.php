<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-22: "Admin, school Admin and Tutor should be able to
 * select the language for each grade... My decision can be changed by the
 * tutor." The language a student sees is still decided per Class (the
 * classes.language_* columns SetStudentLocale reads); these are the
 * Grade's choice, copied to every Class in it when an admin saves it and
 * used as the starting point for Classes added later. A Tutor can then
 * change their own Class.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->string('language_mode', 10)->default('free')->after('grade_level_id'); // free|locked
            $table->string('default_language', 5)->default('en')->after('language_mode');
            $table->string('language_level', 10)->default('standard')->after('default_language'); // standard|beginner
        });
    }

    public function down()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['language_mode', 'default_language', 'language_level']);
        });
    }
};
