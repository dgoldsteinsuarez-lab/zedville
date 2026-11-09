<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The share of the salary a student in the Intermediate (or later) sends to
 * their Emergency Fund each payday (App\Services\SaverService). Null: the
 * usual 20%.
 */
return new class extends Migration {
    public function up()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->unsignedTinyInteger('emergency_fund_percent')->nullable()->after('emergency_fund_account_amount');
        });
    }

    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('emergency_fund_percent');
        });
    }
};
