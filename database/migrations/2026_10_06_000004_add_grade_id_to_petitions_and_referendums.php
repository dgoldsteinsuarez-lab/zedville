<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Visibility becomes 3-tier: class_id set = that one Class; class_id null +
// grade_id set = the whole Grade; both null = whole school (today's only
// option, stays working for existing rows).
return new class extends Migration {
    public function up()
    {
        foreach (['petitions', 'referendums'] as $table) {
            if (!Schema::hasColumn($table, 'grade_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('grade_id')->nullable()->index();
                });
            }
        }
    }

    public function down()
    {
        foreach (['petitions', 'referendums'] as $table) {
            if (Schema::hasColumn($table, 'grade_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('grade_id');
                });
            }
        }
    }
};
