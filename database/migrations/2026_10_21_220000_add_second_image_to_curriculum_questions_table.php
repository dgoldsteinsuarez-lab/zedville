<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-21: "are you sure I can add two graphics to compare in
 * Graphics... sometimes I will compare one graphic, sometimes they just
 * need to answer a question from only one graphic." `image` (previous
 * migration) stays the first/only graphic; this second column is only
 * filled in when a question compares two.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('curriculum_questions', function (Blueprint $table) {
            $table->string('image_2')->nullable()->after('image');
        });
    }

    public function down()
    {
        Schema::table('curriculum_questions', function (Blueprint $table) {
            $table->dropColumn('image_2');
        });
    }
};
