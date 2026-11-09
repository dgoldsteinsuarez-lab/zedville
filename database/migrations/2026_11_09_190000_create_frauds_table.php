<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The frauds, from the Intermediate (proposal «Fraudes», step 15; Daniel,
 * 2026-11-08): a «call» on screen in the student's month 4 of the level and a
 * fake email from the Education Finance Department in month 7, each with one
 * of the common scripts (App\Services\FraudService). One row per fraud a
 * student got; the level's settings sit with the emergencies'.
 */
return new class extends Migration {
    public function up()
    {
        Schema::create('frauds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('sid', 50)->nullable();
            $table->string('channel', 10);                  // call, email
            $table->string('script', 30);                   // FraudService::SCRIPTS
            $table->unsignedTinyInteger('month_in_level');
            $table->string('token', 40)->unique();          // the fake email's link
            $table->dateTime('arrived_at');
            $table->string('status', 20)->default('open'); // open, safe, fell
            $table->string('answer', 30)->nullable();       // what the student did
            $table->unsignedBigInteger('emergency_id')->nullable();   // what falling cost them
            $table->dateTime('answered_at')->nullable();
            $table->timestamps();
        });

        Schema::table('emergency_settings', function (Blueprint $table) {
            $table->boolean('frauds_enabled')->default(true)->after('months_b');
            $table->unsignedTinyInteger('call_month')->default(4)->after('frauds_enabled');
            $table->unsignedTinyInteger('email_month')->default(7)->after('call_month');
        });
    }

    public function down()
    {
        Schema::table('emergency_settings', function (Blueprint $table) {
            $table->dropColumn(['frauds_enabled', 'call_month', 'email_month']);
        });
        Schema::dropIfExists('frauds');
    }
};
