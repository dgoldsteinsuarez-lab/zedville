<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Found by actually walking through the real Citizen Activation
     * (bank account opening) flow — the very first step a new student
     * takes fails with "Unknown column 'sid'" without these.
     */
    public function up()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_accounts', 'sid')) { $table->unsignedBigInteger('sid')->nullable()->after('student_id'); }
            if (!Schema::hasColumn('bank_accounts', 'student_cityzen_id')) { $table->string('student_cityzen_id')->nullable()->after('student_name'); }
        });
    }

    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['sid', 'student_cityzen_id']);
        });
    }
};
