<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zed Directo, the Intermediate's second bank (Daniel, 2026-11-08: "te
 * confirmo"; App\Services\ZedDirecto): a savings account at 6% a year and a
 * fixed-term deposit of 6 months at 7%, which loses its interest if taken out
 * early. Kept with the student's bank account, like the Emergency Fund and the
 * Money Market (App\Services\SavingsLedger), but shown on Zed Directo's own page.
 */
return new class extends Migration {
    public function up()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->decimal('zd_savings_amount', 12, 2)->default(0);
            $table->unsignedTinyInteger('is_open_zd_savings')->default(0);
            $table->string('zd_savings_number', 14)->nullable();
            $table->decimal('zd_deposit_amount', 12, 2)->default(0);
            $table->unsignedTinyInteger('is_open_zd_deposit')->default(0);
            $table->string('zd_deposit_number', 14)->nullable();
            $table->date('zd_deposit_started_on')->nullable();
            $table->date('zd_deposit_matures_on')->nullable();
        });
    }

    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['zd_savings_amount', 'is_open_zd_savings', 'zd_savings_number', 'zd_deposit_amount',
                'is_open_zd_deposit', 'zd_deposit_number', 'zd_deposit_started_on', 'zd_deposit_matures_on']);
        });
    }
};
