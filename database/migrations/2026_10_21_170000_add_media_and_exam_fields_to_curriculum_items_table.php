<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-21: "I should be able to add pictures, graphics and more
 * to them" (PISA/Graphics) + "I want to create an Exam at the end of the
 * year to show or have evidence of learning... add a section of practice
 * exams and real exams in Admin."
 *
 * `image` is the item-level stimulus graphic (PISA's real-life-scenario
 * image, or a Graphics activity's chart) - same upload convention as every
 * other image column in this app (CityMapSetting, Product, etc.): a plain
 * nullable filename string, no Storage facade, see
 * Admin\CurriculumController for the move()-based handling.
 *
 * `is_real_exam`/`start_date`/`end_date` aren't gated to the "Exams"
 * category (curriculum_categories has no stable type column, only a
 * free-text, admin-editable `name` - the exact fragility already solved
 * once via `is_pisa_practice`) - shown on every item form regardless of
 * category, same as `pisa_proficiency_level_id` already is. Dates are
 * display/calendar metadata only, matching mba_position/
 * finhero_activity_registry's existing precedent (not an access window).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('curriculum_items', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description');
            $table->boolean('is_real_exam')->default(false)->after('type');
            $table->date('start_date')->nullable()->after('is_active');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    public function down()
    {
        Schema::table('curriculum_items', function (Blueprint $table) {
            $table->dropColumn(['image', 'is_real_exam', 'start_date', 'end_date']);
        });
    }
};
