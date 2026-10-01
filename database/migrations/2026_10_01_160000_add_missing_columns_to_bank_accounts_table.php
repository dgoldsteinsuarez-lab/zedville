<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Four columns actively used across BankController/OrderController/
     * EFDController (PIN checks, Emergency Fund and Money Market sub-account
     * balances) but never present in any tracked migration for this table.
     * Without card_pin, every PIN check in the app compares against null
     * forever. Without the three *_amount columns, saving a balance update
     * to Emergency Fund or Money Market sub-accounts throws a SQL error.
     * Discovered by actually running the app, not by reading.
     */
    public function up()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('card_pin', 4)->nullable()->after('card_cvv');
            $table->decimal('primary_savings_account_amount', 12, 2)->default(0)->after('primary_savings_account_number');
            $table->decimal('emergency_fund_account_amount', 12, 2)->default(0)->after('emergency_fund_account_number');
            $table->decimal('money_market_account_amount', 12, 2)->default(0)->after('money_market_account_number');
        });
    }

    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'card_pin',
                'primary_savings_account_amount',
                'emergency_fund_account_amount',
                'money_market_account_amount',
            ]);
        });
    }
};
