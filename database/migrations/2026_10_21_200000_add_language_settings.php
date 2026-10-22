<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-21: "I want that the school admin, tutor, can decide if
 * the student can switch languages. For example, if it's use for Language
 * for beginners, the tutor might decide to only have this in that
 * specific language for beginners, to force the students to understand
 * it." A Tutor already owns their Class (class_tutor pivot), so the
 * control lives there: `language_mode` ('free' lets a student pick their
 * own, 'locked' forces everyone in the class to `default_language` with
 * no override) and `default_language` (the starting point either way).
 * Defaults ('free', 'en') change nothing for any existing class until a
 * Tutor/Admin actually sets it.
 *
 * `users.preferred_language` is the student's own choice, only read when
 * their Class is in 'free' mode - ignored entirely under 'locked'.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->string('language_mode', 10)->default('free')->after('status'); // free|locked
            $table->string('default_language', 5)->default('en')->after('language_mode');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('preferred_language', 5)->nullable()->after('nickname');
        });
    }

    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['language_mode', 'default_language']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('preferred_language');
        });
    }
};
