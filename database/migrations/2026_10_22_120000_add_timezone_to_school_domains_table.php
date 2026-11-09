<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Each school's own time zone (Daniel, 2026-10-22: "for each school, I need
 * to add the timezone ... so they get the right greeting"). Defaults to the
 * business time zone already used by the scheduler (Console\Kernel).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_domains', function (Blueprint $table) {
            $table->string('timezone', 64)->default('Europe/Berlin')->after('country_region');
        });
    }

    public function down(): void
    {
        Schema::table('school_domains', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
