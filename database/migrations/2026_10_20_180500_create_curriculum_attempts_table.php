<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ZEDVILLE — Curriculum attempts
 *
 * One row per full sitting of a quiz item (aggregate score), unlike
 * login_quiz_attempts which is one row per single question - a curriculum
 * item can hold many questions, answered and submitted together.
 *
 * item_id cascades on delete, matching login_quiz_attempts' precedent
 * (deleting the content deletes its attempt history) - a deliberate,
 * already-established trade-off in this codebase, not an oversight.
 * Retakes are unlimited (Daniel: "for students to practice") - this table
 * can hold several rows per (item_id, student_id); the composite index
 * below is for "this student's best score on this item" lookups.
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('curriculum_attempts')) {
            return;
        }

        Schema::create('curriculum_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('curriculum_items')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('score');
            $table->unsignedSmallInteger('total_questions');
            $table->dateTime('completed_at');
            $table->timestamps();

            $table->index(['item_id', 'student_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('curriculum_attempts');
    }
};
