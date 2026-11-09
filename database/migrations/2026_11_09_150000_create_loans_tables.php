<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Intermediate's personal loan (proposal «El crédito», step 11; Daniel,
 * 2026-11-08: "de acuerdo"): 500 to 4,000 Ƶ over 6, 12 or 24 months at 8 % TAE,
 * equal instalments (the French system) charged on the 7th with the bills, all
 * of them together at most 35 % of the salary (App\Services\LoanService).
 * Each instalment is kept with its interest and capital, so the student sees
 * the whole table.
 */
return new class extends Migration {
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('sid', 50)->nullable();
            $table->decimal('amount', 12, 2);
            $table->unsignedTinyInteger('months');
            $table->decimal('tae', 6, 4);
            $table->decimal('instalment', 12, 2);
            $table->decimal('interest_total', 12, 2);
            $table->string('status', 20)->default('active');   // active, paid, cancelled
            $table->date('cancel_until');                         // the 14 days to change one's mind
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('loan_instalments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id')->index();
            $table->unsignedTinyInteger('number');
            $table->date('due_on');
            $table->decimal('amount', 12, 2);
            $table->decimal('interest', 12, 2);
            $table->decimal('capital', 12, 2);
            $table->decimal('remaining', 12, 2);
            $table->string('status', 20)->default('pending');   // pending, paid, late, cancelled
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_instalments');
        Schema::dropIfExists('loans');
    }
};
