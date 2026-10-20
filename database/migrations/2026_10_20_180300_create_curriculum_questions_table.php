<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ZEDVILLE — Curriculum questions
 *
 * Clones login_questions' shape (question text + an ordered set of
 * options, see curriculum_question_options), but with real timestamps -
 * login_questions' $timestamps=false was a historical accident already
 * retrofitted once this session (2026_10_19_000003_add_timestamps_to_
 * login_questions.php), no reason to repeat it in brand-new code.
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('curriculum_questions')) {
            return;
        }

        Schema::create('curriculum_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('curriculum_items')->cascadeOnDelete();
            $table->text('question_text');
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('curriculum_questions');
    }
};
