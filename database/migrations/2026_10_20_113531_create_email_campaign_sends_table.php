<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotency log for email_campaigns - relative_to_school_start and
 * recurring_monthly_day campaigns get re-evaluated every day by
 * email-campaigns:dispatch-due, so this is how the dispatcher knows a
 * given student has already received a given campaign for the current
 * cycle (year for relative_to_school_start, month for
 * recurring_monthly_day) and skips re-sending.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('email_campaign_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->dateTime('sent_at');

            $table->index(['campaign_id', 'student_id', 'sent_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_campaign_sends');
    }
};
