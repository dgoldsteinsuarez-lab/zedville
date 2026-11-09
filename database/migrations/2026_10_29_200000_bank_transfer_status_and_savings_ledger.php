<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The bank review (Daniel, 2026-10-29: "check if everything is working,
 * transfers, statement and the other functions"):
 *
 * - transfers get a status and a next run. A "later" or "recurring" transfer
 *   (or bill) runs when it is due, exactly once per date: before, nothing
 *   ever marked one done, so the every-minute scheduler ran each past one
 *   again every minute, and a recurring one looped forever. kind tells a
 *   bill payment from a transfer (both live here). Rows already in the
 *   table: anything whose date has passed is marked done - it has run
 *   already, probably many times - so nothing old runs again.
 *
 * - savings_transactions: the history of the Emergency Fund and the Money
 *   Market account (opening deposit, monthly transfer, transfers in and out,
 *   interest), whose balances live in bank_accounts. Their history used to
 *   be guessed from the current account's lines and didn't add up. Each
 *   open account starts with one "balance brought forward" line.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->string('kind', 20)->default('transfer')->after('type');
            $table->string('status', 20)->default('done')->after('kind');
            $table->dateTime('next_run_at')->nullable()->after('status');
            $table->dateTime('last_run_at')->nullable()->after('next_run_at');
            $table->unsignedInteger('runs')->default(0)->after('last_run_at');
            $table->string('failure', 60)->nullable()->after('runs');
            $table->index(['status', 'next_run_at']);
        });

        $now = now();
        DB::table('transfers')->where('type', 'later')->where('scheduled_at', '>', $now)
            ->update(['status' => 'scheduled', 'next_run_at' => DB::raw('scheduled_at')]);
        DB::table('transfers')->where('type', 'recurring')->where('start_date', '>', $now)
            ->update(['status' => 'scheduled', 'next_run_at' => DB::raw('start_date')]);

        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->string('account', 20); // emergency | money_market
            $table->string('type', 10);    // credit | debit
            $table->string('category', 40);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance', 12, 2);
            $table->string('description');
            $table->json('meta')->nullable();
            $table->dateTime('transaction_date');
            $table->timestamps();
            $table->index(['user_id', 'account', 'transaction_date']);
        });

        foreach (DB::table('bank_accounts')->get() as $account) {
            foreach ([
                'emergency' => ['is_open_emergency_account', 'emergency_fund_account_amount'],
                'money_market' => ['is_open_money_market_account', 'money_market_account_amount'],
            ] as $key => [$open, $amount]) {
                if ((int) $account->{$open} !== 1 || !$account->student_id) {
                    continue;
                }
                DB::table('savings_transactions')->insert([
                    'user_id' => $account->student_id,
                    'bank_account_id' => $account->id,
                    'account' => $key,
                    'type' => 'credit',
                    'category' => 'Brought forward',
                    'amount' => (float) $account->{$amount},
                    'balance' => (float) $account->{$amount},
                    'description' => 'Balance brought forward',
                    'transaction_date' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('savings_transactions');
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropIndex(['status', 'next_run_at']);
            $table->dropColumn(['kind', 'status', 'next_run_at', 'last_run_at', 'runs', 'failure']);
        });
    }
};
