<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-21: Graphics activities need a per-question chart image
 * ("chose the right graphic for a given issue... define the number of the
 * y or x axis") - the item-level `image` (previous migration) is the
 * overall stimulus; this is for a question that shows its own graphic.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('curriculum_questions', function (Blueprint $table) {
            $table->string('image')->nullable()->after('question_text');
        });
    }

    public function down()
    {
        Schema::table('curriculum_questions', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
