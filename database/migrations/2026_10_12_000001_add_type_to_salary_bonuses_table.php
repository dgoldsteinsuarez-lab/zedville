<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Bank Admin Functions doc, Salary Management panel: each income source has
// a Type (Allowance, Bonus, Payment, Other - or a custom role name). Existing
// rows default to 'Bonus' since this table only ever granted bonuses before.
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('salary_bonuses', 'type')) {
            Schema::table('salary_bonuses', function (Blueprint $table) {
                $table->string('type', 50)->default('Bonus')->after('amount');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('salary_bonuses', 'type')) {
            Schema::table('salary_bonuses', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
