<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Transaction1 is the real, live balance ledger — nearly every
     * money-touching controller in the app reads/writes it (see
     * ProjectArchitecture.md section 3). Despite that, no migration for it
     * existed anywhere in this repo; discovered only by actually running the
     * app for the first time against a clean database. Columns match
     * Transaction1::$fillable exactly.
     */
    public function up()
    {
        if (!Schema::hasTable('transactions1')) {
            Schema::create('transactions1', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('sid')->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->dateTime('transaction_date');
            $table->string('description')->nullable();
            $table->string('type')->nullable();
            $table->string('category')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('balance', 12, 2);
            $table->boolean('is_penalty')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'transaction_date']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('transactions1');
    }
};
