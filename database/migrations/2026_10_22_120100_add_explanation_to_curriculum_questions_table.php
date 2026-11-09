<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Why the right answer is right - shown to a student after a practice
 * activity when they choose to check the answers (never for a real exam).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum_questions', function (Blueprint $table) {
            $table->text('explanation')->nullable()->after('question_text');
        });
    }

    public function down(): void
    {
        Schema::table('curriculum_questions', function (Blueprint $table) {
            $table->dropColumn('explanation');
        });
    }
};
