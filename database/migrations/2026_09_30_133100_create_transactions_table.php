<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The older/orphaned ledger (see bugs.md #1, ProjectArchitecture.md §3) —
     * mostly dead, but still live in a few places (e.g. BankController's
     * money-transfer feature). No migration for this existed either; same
     * discovered-by-actually-running-it situation as transactions1. Columns
     * match Transaction::$fillable exactly.
     */
    public function up()
    {
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('sid')->nullable();
            $table->string('type')->nullable();
            $table->dateTime('txn_date')->nullable();
            $table->dateTime('value_date')->nullable();
            $table->string('description')->nullable();
            $table->string('ref_no')->nullable();
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->decimal('balance', 12, 2);
            $table->timestamps();

            $table->index(['user_id', 'txn_date']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
