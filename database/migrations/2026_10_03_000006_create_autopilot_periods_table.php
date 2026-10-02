<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// A recorded period during which a student's account runs on Autopilot
// (approved absence). Tutors are on Autopilot by default and need no row.
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('autopilot_periods')) {
            Schema::create('autopilot_periods', function (Blueprint $table) {
                $table->id();
                $table->string('sid', 50)->nullable()->index();
                $table->unsignedBigInteger('student_id')->index();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->text('reason');
                $table->unsignedBigInteger('created_by');   // Tutor, School Admin, Super Admin or the student
                $table->string('status', 20)->default('active'); // active | cancelled
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('autopilot_periods');
    }
};
