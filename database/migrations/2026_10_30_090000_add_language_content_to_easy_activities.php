<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Language activities for hybrid classes (Daniel, 2026-10-29: "For that, I
 * need to create different activities. Create in Admin a section for these
 * activities"): an easy activity can carry the language it teaches and its
 * content - for "Match the words", the instructions and the word pairs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('easy_activities', function (Blueprint $table) {
            $table->string('language', 5)->nullable()->after('min_amount');
            $table->json('content')->nullable()->after('language');
        });
    }

    public function down(): void
    {
        Schema::table('easy_activities', function (Blueprint $table) {
            $table->dropColumn(['language', 'content']);
        });
    }
};
