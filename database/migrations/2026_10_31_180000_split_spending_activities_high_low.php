<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-10-31: "podemos dividir las tareas en high spending and low
 * spending. Por ejemplo, el teléfono es High Spending o un ordenador" - the
 * phones, computers and instruments are High Spending, the rest Low, each
 * numbered within its kind. A class gets one of each (hba_position,
 * lba_position), both on the Educational Finance Department's board.
 */
return new class extends Migration {
    private const KINDS = [
        'high' => ['Digital Upgrade', 'Music Program Donation', 'Computer Donation', 'Phone Replacement Gift'],
        'low' => ['Birthday Party Budget', 'Community Food Drive', 'Study Space Setup', 'New Sport Starter Kit', 'Room Makeover', 'Closet Restock'],
    ];

    public function up()
    {
        foreach (self::KINDS as $type => $titles) {
            foreach ($titles as $i => $title) {
                DB::table('activities')->where('title', $title)->update(['type' => $type, 'number' => $i + 1]);
            }
        }
        // a class's assignment follows its activity's kind
        $low = DB::table('activities')->where('type', 'low')->pluck('id');
        foreach (DB::table('mba_position')->where('activity_key', 'hba_position')->whereIn('activity_id', $low)->get() as $position) {
            DB::table('calendar_events')->where('classId', $position->cid)->where('title', 'Spending budget activity')
                ->update(['title' => 'Low spending activity', 'description' => 'This is Low spending activity']);
        }
        DB::table('mba_position')->where('activity_key', 'hba_position')->whereIn('activity_id', $low)->update(['activity_key' => 'lba_position']);
    }

    public function down()
    {
        DB::table('mba_position')->where('activity_key', 'lba_position')->update(['activity_key' => 'hba_position']);
        foreach (array_merge(...array_values(self::KINDS)) as $i => $title) {
            DB::table('activities')->where('title', $title)->update(['type' => 'high']);
        }
    }
};
