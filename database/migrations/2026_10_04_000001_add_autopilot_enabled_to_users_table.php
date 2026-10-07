<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// A Tutor's own citizen account runs on Autopilot by default (see AutopilotApply).
// This lets a Tutor switch that off for themselves; defaulting true preserves
// today's behavior for every existing account with no backfill needed.
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('users', 'autopilot_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('autopilot_enabled')->default(true)->after('role');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'autopilot_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('autopilot_enabled');
            });
        }
    }
};
