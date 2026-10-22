<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Academy "Monthly Questions": students practise the Log In questions month
 * by month (a month opens when the school reaches it and stays open), plus
 * a final mixed review. Kept apart from login_quiz_attempts on purpose -
 * that table drives the once-a-day pop-up and its FinHero points.
 * month_number null = the final mixed review.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('login_practice_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('month_number')->nullable();
            $table->unsignedSmallInteger('score');
            $table->unsignedSmallInteger('total');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->index(['student_id', 'month_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('login_practice_attempts');
    }
};
