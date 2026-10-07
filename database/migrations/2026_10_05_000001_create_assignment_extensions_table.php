<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Admin Roles & Permissions doc §2.3: Tutor can "Grant assignment due-date
// extensions with reason." No due-date concept exists on calendar_events
// today (only start/end), so this is a new per-student override table
// rather than a column on calendar_events itself.
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('assignment_extensions')) {
            Schema::create('assignment_extensions', function (Blueprint $table) {
                $table->id();
                $table->string('sid', 50)->nullable()->index();
                $table->unsignedBigInteger('calendar_event_id')->index();
                $table->unsignedBigInteger('student_id')->index();
                $table->dateTime('original_due')->nullable();
                $table->dateTime('extended_to');
                $table->text('reason');
                $table->unsignedBigInteger('granted_by');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('assignment_extensions');
    }
};
