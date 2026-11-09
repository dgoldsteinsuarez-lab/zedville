<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The credit card, from the Intermediate (proposal «Tarjeta de crédito», step
 * 16; agreed 2026-11-08: a 1,000 Ƶ limit, paying it all on the 7th costs
 * nothing, deferring costs 20 % TAE with a 3 % minimum payment). The card, its
 * movements (purchases, payments, interest; balance = what is owed) and its
 * monthly statements (App\Services\CreditCardService).
 */
return new class extends Migration {
    public function up()
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('sid', 50)->nullable();
            $table->string('number', 30);
            $table->decimal('limit', 10, 2);
            $table->string('mode', 10)->default('full');     // full, minimum, fixed
            $table->decimal('fixed_amount', 10, 2)->nullable();
            $table->string('status', 10)->default('active'); // active, closed
            $table->timestamps();
        });

        Schema::create('card_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('type', 10);                      // purchase, payment, interest
            $table->decimal('amount', 10, 2);
            $table->decimal('balance', 10, 2);               // owed after it
            $table->string('description');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->dateTime('moved_at');
            $table->timestamps();
        });

        Schema::create('card_statements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('month', 7);                      // the month it closes, Y-m
            $table->decimal('balance', 10, 2);               // owed when it closed
            $table->decimal('due', 10, 2);                   // to pay on the 7th, by the card's mode
            $table->decimal('paid', 10, 2)->default(0);
            $table->decimal('interest', 10, 2)->default(0);  // on what was deferred
            $table->string('status', 10)->default('pending'); // pending, paid, late
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['card_id', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('card_statements');
        Schema::dropIfExists('card_movements');
        Schema::dropIfExists('credit_cards');
    }
};
