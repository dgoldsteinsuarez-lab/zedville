<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// The live database already has these widths; the original migration used 10.
// Brings fresh/local databases in line (no-op when already wide enough).
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('bank_accounts')) {
            return;
        }
        foreach (['primary_savings_account_number' => 13, 'emergency_fund_account_number' => 12, 'money_market_account_number' => 12] as $col => $len) {
            if (Schema::hasColumn('bank_accounts', $col)) {
                DB::statement("ALTER TABLE bank_accounts MODIFY {$col} VARCHAR({$len}) NULL");
            }
        }
    }

    public function down()
    {
    }
};
