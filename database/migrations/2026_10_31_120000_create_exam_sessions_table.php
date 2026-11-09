<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// A real exam opened by a tutor for one class on the day of the exam, with a
// password they give the students; the students see their mark once the
// tutor releases the results (Daniel, 2026-10-31).
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('exam_sessions')) {
            Schema::create('exam_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('curriculum_items')->cascadeOnDelete();
                $table->unsignedBigInteger('class_id');
                $table->date('exam_date');
                $table->string('password', 20);
                $table->unsignedBigInteger('opened_by');
                $table->dateTime('closed_at')->nullable();
                $table->dateTime('results_released_at')->nullable();
                $table->timestamps();

                $table->unique(['item_id', 'class_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('exam_sessions');
    }
};
