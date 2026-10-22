<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Questions belong to a Library module, not to one assignment of it, so a
 * module's quiz is authored once and every class it's assigned to sees the
 * same questions. Same shape as curriculum_questions.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('finhero_library_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('finhero_library_modules')->cascadeOnDelete();
            $table->text('question_text');
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('finhero_library_questions');
    }
};
