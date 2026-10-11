<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * mood_logs.sid exists in the live dev database (written by
 * MoodController::saveMode()) but had no migration - caught while
 * adding test coverage for saveMode()/city_mood() during the
 * ProfileController -> MoodController extraction (planning.md Phase 3
 * #4). Same drift pattern as the 31-table, users.sid, and
 * bank_accounts findings earlier.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('mood_logs', 'sid')) {
            Schema::table('mood_logs', function ($table) {
                $table->string('sid', 50)->nullable()->after('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mood_logs', 'sid')) {
            Schema::table('mood_logs', function ($table) {
                $table->dropColumn('sid');
            });
        }
    }
};
