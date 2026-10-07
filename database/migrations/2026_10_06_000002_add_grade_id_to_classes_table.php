<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('classes', 'grade_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->unsignedBigInteger('grade_id')->nullable()->index()->after('grade_level_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('classes', 'grade_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropColumn('grade_id');
            });
        }
    }
};
