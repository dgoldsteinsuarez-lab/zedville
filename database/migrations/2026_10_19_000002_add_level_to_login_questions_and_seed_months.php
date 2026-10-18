<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel (2026-10-19): Log In Questions are fixed per Level (currently
 * only Rookie is in use, since no other Level has real content yet), and
 * which real calendar month corresponds to "month 1 of that Level's
 * progression" is set per school (school_month_settings.start_month,
 * already built - just never wired to question selection).
 * login_questions had no Level column at all - added here. `months` is
 * seeded with 12 generic progression-month labels (just numbering
 * infrastructure, not real curriculum content - the actual questions
 * themselves are still pending real content from Daniel).
 */
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('login_questions', 'grade_level_id')) {
            Schema::table('login_questions', function (Blueprint $table) {
                $table->unsignedBigInteger('grade_level_id')->nullable()->after('sid');
            });
        }

        for ($i = 1; $i <= 12; $i++) {
            if (!DB::table('months')->where('name', "Month {$i}")->exists()) {
                DB::table('months')->insert(['name' => "Month {$i}"]);
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('login_questions', 'grade_level_id')) {
            Schema::table('login_questions', function (Blueprint $table) {
                $table->dropColumn('grade_level_id');
            });
        }
    }
};
