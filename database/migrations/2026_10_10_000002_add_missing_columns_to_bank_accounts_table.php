<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * bank_accounts.student_prefered_name, is_open_emergency_account, and
 * is_open_money_market_account exist in the live dev database but had no
 * migration - a fresh migrate (what RefreshDatabase-based tests run)
 * couldn't reproduce them. The latter two aren't guarded by
 * BankAccount::$fillable, so a controller's Model::where(...)->update([...])
 * call (which bypasses mass-assignment guarding entirely) hit a real
 * "Unknown column" SQL error against a freshly-migrated database -
 * caught while testing BankController::emengercyfundaccount().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function ($table) {
            if (!Schema::hasColumn('bank_accounts', 'student_prefered_name')) {
                $table->string('student_prefered_name')->nullable()->after('student_cityzen_id');
            }
            if (!Schema::hasColumn('bank_accounts', 'is_open_emergency_account')) {
                $table->integer('is_open_emergency_account')->default(0)->after('emergency_fund_account_amount');
            }
            if (!Schema::hasColumn('bank_accounts', 'is_open_money_market_account')) {
                $table->integer('is_open_money_market_account')->default(0)->after('money_market_account_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function ($table) {
            foreach (['student_prefered_name', 'is_open_emergency_account', 'is_open_money_market_account'] as $column) {
                if (Schema::hasColumn('bank_accounts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
