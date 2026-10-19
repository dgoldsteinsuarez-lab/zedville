<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * City Map, Phase 1 (planning.md / the "City and Map" doc, 2026-10-19).
 * `streets` is a fixed, global reference list (27 names) - same category
 * as `GradeLevel`, not per-school/per-grade content. `addresses` is the
 * actual street+number a student lives at, scoped per Grade: two students
 * in different Grades can validly share the same street+number, since
 * each Grade is its own simulated city (same model `CityProposal` already
 * uses for the per-Grade city name/quote).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('streets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tier'); // '1'..'5' or 'marxist', matches the doc's tier grouping
            $table->text('bio');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('street_id')->constrained('streets')->cascadeOnDelete();
            $table->unsignedInteger('house_number');
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['grade_id', 'street_id', 'house_number']);
        });

        Schema::table('grades', function (Blueprint $table) {
            // How many addresses this Grade's city has in total, spread
            // evenly across the 27 streets. Nullable - AddressService
            // falls back to a sane default (200) when not set, so this
            // never blocks a Grade that hasn't configured it explicitly.
            $table->unsignedInteger('max_addresses')->nullable()->after('grade_level_id');
        });
    }

    public function down()
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('max_addresses');
        });
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('streets');
    }
};
