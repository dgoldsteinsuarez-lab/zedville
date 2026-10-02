<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** No tracked migration existed — discovered testing badges:calculate-monthly. */
    public function up()
    {
        if (!Schema::hasTable('student_badge_records')) {
            Schema::create('student_badge_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->string('academic_year')->nullable();
            $table->unsignedInteger('required_assigned')->default(0);
            $table->unsignedInteger('required_completed')->default(0);
            $table->unsignedInteger('optional_completed')->default(0);
            $table->unsignedInteger('participation_points')->default(0);
            $table->string('monthly_badge')->default('NONE');
            $table->unsignedTinyInteger('monthly_badge_points')->default(0);
            $table->unsignedInteger('accumulated_points')->default(0);
            $table->string('accumulated_badge')->default('NONE');
            $table->boolean('is_overridden')->default(false);
            $table->string('override_badge')->nullable();
            $table->dateTime('overridden_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'month', 'year']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('student_badge_records');
    }
};
