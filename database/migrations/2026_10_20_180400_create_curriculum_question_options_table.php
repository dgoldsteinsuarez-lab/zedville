<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ZEDVILLE — Curriculum question options
 *
 * Mirrors login_options' fields exactly (option_text + is_correct - the
 * scoring primitive), with real timestamps (see curriculum_questions).
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('curriculum_question_options')) {
            return;
        }

        Schema::create('curriculum_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('curriculum_questions')->cascadeOnDelete();
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('curriculum_question_options');
    }
};
