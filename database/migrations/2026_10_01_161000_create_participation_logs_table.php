<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * No model/tracked migration existed — ParticipationService uses
     * DB::table() directly. Columns inferred from every insert() call in
     * that file. No tracked migration existed - discovered by actually
     * running the app.
     */
    public function up()
    {
        Schema::create('participation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('action_type')->nullable();
            $table->string('action_detail')->nullable();
            $table->unsignedInteger('points_earned')->default(0);
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('academic_year')->nullable();
            $table->dateTime('earned_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'month', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('participation_logs');
    }
};
