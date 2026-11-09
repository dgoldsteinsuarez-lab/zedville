<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Activities by level (Daniel, 2026-11-02: "hay que dividir ya las actividades
 * que son para cada nivel rookie, intermedio, etc." - the Admin menu by level,
 * App\Support\AdminLevel). `levels`: the grade levels an activity is for, null
 * for every level. Known ones get theirs: the Money Factory's exchange
 * activities and its new banknote tracker (Junior to Intermediate) and the
 * Rookie Library modules; the rest stays for every level until an admin says.
 */
return new class extends Migration {
    private const TABLES = ['easy_activities', 'activities', 'finhero_library_modules'];

    public function up()
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'levels')) {
                Schema::table($table, fn (Blueprint $t) => $t->json('levels')->nullable());
            }
        }

        $ids = fn (array $names) => DB::table('grade_levels')->whereIn('name', $names)->orderBy('sort_order')->pluck('id')->map(fn ($id) => (int) $id)->all() ?: null;
        $first = $ids(['Junior', 'Rookie', 'Intermediate']);

        DB::table('easy_activities')->where('type', 'currency_exchange')->whereNull('levels')
            ->update(['levels' => $first ? json_encode($first) : null]);
        DB::table('easy_activities')->insertOrIgnore([
            'type' => 'mf_journey', 'title' => 'Where has this banknote been?', 'levels' => $first ? json_encode($first) : null,
            'sid' => null, 'created_by' => null, 'created_at' => now(), 'updated_at' => now(),
        ]);

        if ($rookie = $ids(['Rookie'])) {
            DB::table('finhero_library_modules')->whereNull('levels')->update(['levels' => json_encode($rookie)]);
        }
    }

    public function down()
    {
        $ids = DB::table('easy_activities')->where('type', 'mf_journey')->pluck('id');
        DB::table('mba_position')->whereIn('activity_key', $ids->map(fn ($id) => 'easy_' . $id))->delete();
        DB::table('calendar_events')->where('title', 'easy-Where has this banknote been?')->delete();
        DB::table('easy_activities')->whereIn('id', $ids)->delete();

        foreach (self::TABLES as $table) {
            if (Schema::hasColumn($table, 'levels')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn('levels'));
            }
        }
    }
};
