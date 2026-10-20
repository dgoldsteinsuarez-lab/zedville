<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ZEDVILLE — Curriculum items (one Activity/Exam/Graphic within a category)
 *
 * `type`: 'quiz' (Activities/Exams - a real scored question bank, see
 * curriculum_questions/curriculum_question_options) or 'content' (for
 * Graphics - just a title/description, no scoring; no file upload in v1).
 *
 * `pisa_proficiency_level_id` lets any item, in any level's category,
 * optionally be calibrated against the OECD PISA scale built earlier today
 * (pisa_proficiency_levels) - this is what makes "practice exams like in
 * the real pisa exams" concrete, and is how "PISA Practice" category items
 * get tagged.
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('curriculum_items')) {
            return;
        }

        Schema::create('curriculum_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('curriculum_categories')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['quiz', 'content'])->default('quiz');
            $table->foreignId('pisa_proficiency_level_id')->nullable()->constrained('pisa_proficiency_levels')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('curriculum_items');
    }
};
