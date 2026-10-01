<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * No model/tracked migration existed - BadgeCalculatorService uses
     * DB::table() directly. Columns inferred from every query against it
     * in BadgeCalculatorService/FinheroBadgeCalculatorService.
     */
    public function up()
    {
        Schema::create('activity_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->string('activity_type')->nullable();
            $table->boolean('optional')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'completed_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_completions');
    }
};
