<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ZEDVILLE — Curriculum attempt answers
 *
 * Per-question breakdown within one curriculum_attempts row (for a
 * results/review screen). question_id/option_id both null on delete
 * rather than cascade - editing or removing a single question/option
 * later shouldn't silently shrink an already-completed attempt's history;
 * is_correct is already captured permanently on the row at submission time.
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('curriculum_attempt_answers')) {
            return;
        }

        Schema::create('curriculum_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('curriculum_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->nullable()->constrained('curriculum_questions')->nullOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('curriculum_question_options')->nullOnDelete();
            $table->boolean('is_correct')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('curriculum_attempt_answers');
    }
};
