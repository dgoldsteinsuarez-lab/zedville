<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A scheduled or immediate send of one email_templates row to a resolved
 * set of recipients. See the "Email Template / campaign system rebuild"
 * plan (bugs.md, 2026-10-20) for the full picture - this replaces the old
 * admin/email-template "communication" one-off blast tool with something
 * that can target a school, a grade, or wait for a date (fixed, or
 * computed per school from school_month_settings.start_month).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            // email_templates.id is INT UNSIGNED (a pre-existing column,
            // not Laravel's default bigIncrements) - matching the type
            // exactly here since MySQL requires it for the FK below.
            $table->unsignedInteger('template_id');
            $table->foreign('template_id')->references('id')->on('email_templates')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedBigInteger('created_by')->nullable();

            // Recipient scope
            $table->enum('scope_type', ['all', 'school', 'grade', 'selected']);
            $table->unsignedBigInteger('scope_school_id')->nullable();
            $table->unsignedBigInteger('scope_grade_id')->nullable();
            $table->json('selected_user_ids')->nullable();

            // Trigger / schedule
            $table->enum('trigger_type', ['immediate', 'fixed_date', 'relative_to_school_start', 'recurring_monthly_day']);
            $table->dateTime('fixed_date')->nullable();
            $table->unsignedSmallInteger('relative_days_offset')->nullable();
            $table->unsignedTinyInteger('recurring_day_of_month')->nullable();

            $table->enum('status', ['scheduled', 'sent', 'cancelled'])->default('scheduled');
            $table->dateTime('last_run_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'trigger_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_campaigns');
    }
};
