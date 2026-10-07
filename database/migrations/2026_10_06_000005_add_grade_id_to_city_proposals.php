<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Changing the city name/quote is always Grade-wide (never per-class, never
// per-school) - city_proposals moves from sid-scoped to grade_id-scoped.
// Backfills existing rows' grade_id from the proposer's class->grade.
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('city_proposals', 'grade_id')) {
            Schema::table('city_proposals', function (Blueprint $table) {
                $table->unsignedBigInteger('grade_id')->nullable()->index();
            });
        }

        $proposals = DB::table('city_proposals')->whereNull('grade_id')->get();

        foreach ($proposals as $proposal) {
            $proposer = DB::table('users')->where('id', $proposal->proposed_by)->first();
            if (!$proposer || !$proposer->grade) {
                continue;
            }

            $class = DB::table('classes')->where('id', $proposer->grade)->first();
            if (!$class || !$class->grade_id) {
                continue;
            }

            DB::table('city_proposals')->where('id', $proposal->id)->update(['grade_id' => $class->grade_id]);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('city_proposals', 'grade_id')) {
            Schema::table('city_proposals', function (Blueprint $table) {
                $table->dropColumn('grade_id');
            });
        }
    }
};
