<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Supports the async leaderboard's monthly best-score-per-student query
 * (student_id + completed_at range) - see Phase 4, Student\PisaController
 * or wherever the leaderboard query lands.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('curriculum_attempts', function (Blueprint $table) {
            $table->index(['student_id', 'completed_at']);
        });
    }

    public function down()
    {
        Schema::table('curriculum_attempts', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'completed_at']);
        });
    }
};
