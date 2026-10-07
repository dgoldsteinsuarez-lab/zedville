<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Admin Roles & Permissions doc §7: inactive student data should be
// anonymised within 12 months of last activity. This column marks a user
// as already anonymized (idempotency for the scheduled job) without ever
// deleting their historical ledger/badge/petition rows.
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('users', 'anonymized_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('anonymized_at')->nullable()->after('loginTime');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'anonymized_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('anonymized_at');
            });
        }
    }
};
