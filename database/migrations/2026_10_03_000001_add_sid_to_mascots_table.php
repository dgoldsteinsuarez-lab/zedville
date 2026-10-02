<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// AdminStudentController filters mascots by school; the column was never migrated.
// NULL = mascot shared by every school.
return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('mascots') && !Schema::hasColumn('mascots', 'sid')) {
            Schema::table('mascots', function (Blueprint $table) {
                $table->unsignedBigInteger('sid')->nullable()->index();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('mascots', 'sid')) {
            Schema::table('mascots', function (Blueprint $table) {
                $table->dropColumn('sid');
            });
        }
    }
};
