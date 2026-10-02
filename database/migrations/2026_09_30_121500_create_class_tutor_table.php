<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Many-to-many: a class can have more than one tutor, and a tutor can be
     * assigned to more than one class (school-configurable, per product decision
     * on 2026-09-30 — e.g. a grade with two classes where both tutors cover both).
     */
    public function up()
    {
        if (!Schema::hasTable('class_tutor')) {
            Schema::create('class_tutor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['class_id', 'user_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('class_tutor');
    }
};
