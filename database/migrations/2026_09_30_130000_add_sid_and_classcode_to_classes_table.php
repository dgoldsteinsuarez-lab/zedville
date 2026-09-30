<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * These columns are already relied on throughout the live code
     * (RegisteredUserController, AdminDashboardController, AdminStudentController)
     * but were never present in any tracked migration for this table — the
     * live/test database apparently has them from an untracked schema change.
     * Adding them here so a database built from these migrations alone actually
     * matches what the running code expects.
     */
    public function up()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedBigInteger('sid')->nullable()->after('id');
            $table->string('classCode', 6)->nullable()->unique()->after('sid');
        });
    }

    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['sid', 'classCode']);
        });
    }
};
