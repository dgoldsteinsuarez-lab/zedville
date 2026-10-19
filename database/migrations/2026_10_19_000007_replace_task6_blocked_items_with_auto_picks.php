<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Substitute Goods / FinHero Task 6, reworked 2026-10-19: Daniel doesn't
 * want an admin/tutor to manually pick which catalog items are blocked
 * for a Class - "quiero que lo automatices tu, que tomes los productos
 * de cada alumno y escogas cuales debe sustituir" (automate it, take
 * each student's own products and choose which they must substitute).
 *
 * Replaces the per-Class `finhero_task6_blocked_items` table (never
 * actually populated - no admin screen ever wrote to it before this
 * rework) with a per-student table: which of THAT student's own basket
 * items the system auto-picked for them, regenerated once per month
 * while Task 6 is active. An admin/tutor still only needs to activate
 * Task 6 the same way they activate any other task (finhero_activity_
 * registry.is_active) - nothing else.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('finhero_task6_blocked_items');

        Schema::create('finhero_task6_student_picks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('supermarket_id')->constrained('supermarkets')->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->timestamps();

            $table->unique(['student_id', 'supermarket_id', 'month', 'year'], 'task6_picks_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('finhero_task6_student_picks');

        Schema::create('finhero_task6_blocked_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sid');
            $table->unsignedBigInteger('cid');
            $table->foreignId('supermarket_id')->constrained('supermarkets')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['sid', 'cid', 'supermarket_id']);
        });
    }
};
