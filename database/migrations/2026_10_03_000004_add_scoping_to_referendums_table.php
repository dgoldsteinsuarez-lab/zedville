<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The live `referendums` table predates school/class scoping. Needed so a
// referendum can be limited to one school (sid) or one class (class_id).
return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('referendums')) {
            Schema::table('referendums', function (Blueprint $table) {
                if (!Schema::hasColumn('referendums', 'sid')) { $table->unsignedBigInteger('sid')->nullable(); }
                if (!Schema::hasColumn('referendums', 'class_id')) { $table->unsignedBigInteger('class_id')->nullable(); }
            });
        }
    }

    public function down()
    {
        Schema::table('referendums', function (Blueprint $table) {
            $table->dropColumn(['sid', 'class_id']);
        });
    }
};
