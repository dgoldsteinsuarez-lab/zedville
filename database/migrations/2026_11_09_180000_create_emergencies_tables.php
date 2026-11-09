<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The emergencies, from the Intermediate (proposal «El imprevisto», step 13;
 * Daniel, 2026-11-08: "de acuerdo", 30 days to pay, no fine): one row per
 * emergency a student gets (App\Services\EmergencyService), and the admin's
 * settings per level - on or off, and the months of each scenario.
 */
return new class extends Migration {
    public function up()
    {
        Schema::create('emergencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('sid', 50)->nullable();
            $table->unsignedTinyInteger('number');          // 1st, 2nd, 3rd of the year
            $table->unsignedTinyInteger('month_in_level');  // the student's month in the level when it came
            $table->string('kind', 30);                     // EmergencyService::KINDS
            $table->decimal('cost', 10, 2);                 // what it costs
            $table->decimal('amount', 10, 2);               // what the student pays (health: after the insurance)
            $table->string('plan', 20)->nullable();         // the health insurance plan then
            $table->boolean('waiting')->default(false);     // its waiting period for the dentist wasn't over
            $table->dateTime('arrived_at');
            $table->date('due_on');                         // the last of the 30 days
            $table->string('status', 20)->default('pending');   // pending, paid, auto
            $table->string('method', 20)->nullable();       // fund, fund_current, current, loan; auto: current, overdraft
            $table->unsignedBigInteger('loan_id')->nullable();
            $table->decimal('interest', 10, 2)->default(0); // the loan's interest, when paid with one
            $table->dateTime('paid_at')->nullable();
            $table->string('change', 30)->nullable();       // what the student would do differently
            $table->dateTime('completed_at')->nullable();   // paid and both questions answered
            $table->timestamps();
        });

        Schema::create('emergency_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grade_level_id')->unique();
            $table->boolean('enabled')->default(true);
            $table->string('months_a', 50);   // "3,6,9": the student's months in the level, scenario A
            $table->string('months_b', 50);   // "5,7,9": scenario B
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('emergency_settings');
        Schema::dropIfExists('emergencies');
    }
};
