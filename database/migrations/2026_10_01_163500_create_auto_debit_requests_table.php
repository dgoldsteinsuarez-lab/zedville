<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** No tracked migration existed — discovered walking the real Citizen Activation flow. */
    public function up()
    {
        if (!Schema::hasTable('auto_debit_requests')) {
            Schema::create('auto_debit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('sid')->nullable();
            $table->string('type')->nullable();
            $table->string('fullname')->nullable();
            $table->string('email')->nullable();
            $table->string('accountno')->nullable();
            $table->text('serviceaddress')->nullable();
            $table->string('bankname')->nullable();
            $table->string('backaccountnumber')->nullable();
            $table->boolean('billschedule')->nullable();
            $table->boolean('fixedpayment')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->boolean('tandagree')->default(false);
            $table->string('signature')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('auto_debit_requests');
    }
};
