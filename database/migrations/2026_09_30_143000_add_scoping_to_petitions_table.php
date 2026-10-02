<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Petitions had no school/class scoping at all - any Tutor or School
     * Admin could see every school's petitions. Adding the columns here;
     * the actual filtering logic lives in CivicChamberController.
     */
    public function up()
    {
        Schema::table('petitions', function (Blueprint $table) {
            if (!Schema::hasColumn('petitions', 'sid')) { $table->unsignedBigInteger('sid')->nullable()->after('created_by'); }
            if (!Schema::hasColumn('petitions', 'class_id')) { $table->unsignedBigInteger('class_id')->nullable()->after('sid'); }
        });
    }

    public function down()
    {
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropColumn(['sid', 'class_id']);
        });
    }
};
