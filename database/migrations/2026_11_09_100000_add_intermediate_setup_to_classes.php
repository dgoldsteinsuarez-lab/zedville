<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The month a class started the Intermediate, which the level's months count
 * from (App\Services\IntermediateClassService). Empty: the start of the school
 * year. Each student's scenario - A, with a fund from the Rookie; B, without -
 * comes from their own bank, not from here (Daniel, 2026-11-08: "debería
 * hacerse por estudiante").
 */
return new class extends Migration {
    public function up()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->date('intermediate_started_on')->nullable()->after('spending4');
        });
    }

    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('intermediate_started_on');
        });
    }
};
