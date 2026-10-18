<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Schema drift found 2026-10-19 (same pattern as earlier passes this
 * session): the live dev DB's login_questions has a created_at column
 * someone added directly, never captured in the tracked migration - so
 * the isolated test DB didn't have it, and AdminDashboardController::
 * login_question()'s ->latest() (orders by created_at) broke there. Adds
 * created_at/updated_at properly; LoginQuestion keeps `$timestamps =
 * false` (the app never writes these itself, same as before), so
 * ->latest() falls back to being a plain, now-working DB-level default.
 */
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('login_questions', 'created_at')) {
            Schema::table('login_questions', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable()->useCurrent();
            });
        }
        if (!Schema::hasColumn('login_questions', 'updated_at')) {
            Schema::table('login_questions', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::table('login_questions', function (Blueprint $table) {
            if (Schema::hasColumn('login_questions', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
