<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The rest of the Money Factory's activities (Daniel, 2026-11-02: "haz también
 * lo que falta"), built in like the banknote tracker: admins assign them from
 * a level's Money Factory screen. Real or fake banknote, the story of money
 * and prices in other currencies for Junior to Intermediate; the rest for
 * Advance and Advance Economist. An admin can change the levels.
 */
return new class extends Migration {
    private const ACTIVITIES = [
        'mf_fake' => ['Real or fake banknote?', ['Junior', 'Rookie', 'Intermediate']],
        'mf_history' => ['The story of money', ['Junior', 'Rookie', 'Intermediate']],
        'mf_prices' => ['How much is it in other currencies?', ['Junior', 'Rookie', 'Intermediate']],
        'mf_roundtrip' => ['Back from the trip: buy and sell prices', ['Advance', 'Advance Economist']],
        'mf_bigmac' => ['The Big Mac index', ['Advance', 'Advance Economist']],
        'mf_headlines' => ['Headlines that move the Zed', ['Advance', 'Advance Economist']],
        'mf_central_bank' => ['Central bank for a day', ['Advance', 'Advance Economist']],
        'mf_print_or_cut' => ['Print money or cut spending?', ['Advance', 'Advance Economist']],
    ];

    public function up()
    {
        foreach (self::ACTIVITIES as $type => [$title, $levels]) {
            if (DB::table('easy_activities')->where('type', $type)->exists()) {
                continue;
            }
            $ids = DB::table('grade_levels')->whereIn('name', $levels)->orderBy('sort_order')->pluck('id')->map(fn ($id) => (int) $id)->all();
            DB::table('easy_activities')->insert([
                'type' => $type, 'title' => $title, 'levels' => $ids ? json_encode($ids) : null,
                'sid' => null, 'created_by' => null, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        $ids = DB::table('easy_activities')->whereIn('type', array_keys(self::ACTIVITIES))->pluck('id');
        DB::table('mba_position')->whereIn('activity_key', $ids->map(fn ($id) => 'easy_' . $id))->delete();
        DB::table('calendar_events')->whereIn('title', array_map(fn ($a) => 'easy-' . $a[0], self::ACTIVITIES))->delete();
        DB::table('easy_activities')->whereIn('id', $ids)->delete();
    }
};
