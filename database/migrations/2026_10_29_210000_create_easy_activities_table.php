<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Easy activities (Daniel, 2026-10-29: "Under easy activities, with
 * Engagement Badge, we can start adding activities in Admin, like this
 * transfer activity"): short real-life tasks in the city an admin creates
 * and assigns to classes. The type says what the student does and how the
 * app sees it's done (bank_transfer: send money to another student).
 * Assignments are mba_position rows (activity_key "easy_<id>") with the
 * matching calendar event, like the other Engagement activities.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('easy_activities', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);
            $table->string('title', 120)->unique();
            $table->decimal('min_amount', 10, 2)->nullable();
            // null: every school's admins see it (made by a super admin)
            $table->unsignedBigInteger('sid')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('easy_activities');
    }
};
