<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Intermediate's health insurance (proposal «El seguro médico», step 12;
 * Daniel, 2026-11-08: "de acuerdo"): Basic or Complete, the premium with the
 * bills, changed at most once a year (App\Services\InsuranceService). One row
 * per plan the student has had; the one without ended_on is the current one.
 * auto: the City Hall gave it (not chosen in the first month); the student
 * may still choose once.
 */
return new class extends Migration {
    public function up()
    {
        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('sid', 50)->nullable();
            $table->string('plan', 20);   // basic, complete
            $table->boolean('auto')->default(false);
            $table->date('started_on');
            $table->date('ended_on')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('insurance_policies');
    }
};
