<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-19: "how are you doing with the engagement badge, is
 * it all good?" - found a second bug while checking: ProfileController::
 * calculateMonthlyEngagement() only ever counts a participation_logs row
 * toward "optional activities" when `action_type` literally equals
 * 'optional'. But ParticipationService::award() - the method every real
 * call site uses (transfer, donation, poster_room_enter, emergency_fund_
 * open, money_market_open, and the new substitute_goods_swap) - was
 * storing the specific action name itself in `action_type` (e.g.
 * 'donation'), never the literal string 'optional'. Confirmed against
 * live data: all 4 existing rows have action_type of 'donation',
 * 'poster_room_enter', or 'transfer' - none of them were ever counted.
 *
 * ParticipationService::handleActivity() (the other, calendar-event-
 * driven path) already used the *correct* convention - action_type is
 * the 'required'/'optional' bucket, action_detail is the specific key.
 * This migration adds `action_ref` (for the few action types that also
 * need a sub-identifier - shop id, NPO name, building id, sign id - a
 * field award()'s $detail param already carried, previously overwriting
 * nothing since it just went into the same action_detail column
 * handleActivity() uses for the key itself) and backfills every existing
 * award()-sourced row onto the corrected convention.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('participation_logs', function (Blueprint $table) {
            $table->string('action_ref')->nullable()->after('action_detail');
        });

        // Every existing row came through award() (handleActivity() was
        // never actually wired to a live call site), so action_type here
        // is always the specific key, never already 'required'/'optional'.
        DB::table('participation_logs')->update([
            'action_ref' => DB::raw('action_detail'),
        ]);
        DB::table('participation_logs')->update([
            'action_detail' => DB::raw('action_type'),
            'action_type' => 'optional',
        ]);
    }

    public function down()
    {
        DB::table('participation_logs')->update([
            'action_type' => DB::raw('action_detail'),
            'action_detail' => DB::raw('action_ref'),
        ]);

        Schema::table('participation_logs', function (Blueprint $table) {
            $table->dropColumn('action_ref');
        });
    }
};
