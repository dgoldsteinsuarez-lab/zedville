<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * No tracked migration existed — discovered walking the real Citizen
     * Activation flow. This is a reference/lookup table (billers the
     * auto-debit step queries by exact name), so seeding the four names
     * the live code actually looks for, not just creating the table.
     */
    public function up()
    {
        if (!Schema::hasTable('manage_payees_biller')) {
            Schema::create('manage_payees_biller', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sid')->nullable();
            $table->string('name');
            $table->string('account_number')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
            });
        }

        DB::table('manage_payees_biller')->insert([
            ['name' => 'City School', 'account_number' => 'SCHOOL-AUTO', 'amount' => 1000, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Internet Service Provider', 'account_number' => 'ISP-AUTO', 'amount' => 20, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Utility - Electricity', 'account_number' => 'ELEC-AUTO', 'amount' => 75, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Utility - Water', 'account_number' => 'WATER-AUTO', 'amount' => 45, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('manage_payees_biller');
    }
};
