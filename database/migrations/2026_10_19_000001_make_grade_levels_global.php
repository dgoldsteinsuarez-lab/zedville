<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel (2026-10-19): the four curriculum Levels (Rookie, Intermediate,
 * Advance, Advance Economist) are the same fixed set everywhere - not
 * something each school types in independently. Which Level applies to a
 * given school's Grade stays a per-school, per-Grade choice (Grade.
 * grade_level_id already does that correctly) - only the LIST of Levels to
 * choose from was wrongly modeled as per-school free text, which is why
 * the only live row was a school admin mistakenly typing "Grade 8" into
 * what should have been a Level picker with nothing to pick from.
 */
return new class extends Migration {
    public function up()
    {
        // The one existing bad row ("Grade 8", created by an admin with no
        // real options to choose from) - any Grade pointing at it reverts
        // to unassigned rather than keeping a nonsense Level.
        if (DB::table('grade_levels')->where('name', 'Grade 8')->exists()) {
            $badId = DB::table('grade_levels')->where('name', 'Grade 8')->value('id');
            DB::table('grades')->where('grade_level_id', $badId)->update(['grade_level_id' => null]);
            DB::table('grade_levels')->where('id', $badId)->delete();
        }

        DB::statement('ALTER TABLE grade_levels DROP INDEX grade_levels_sid_name_unique');
        DB::statement('ALTER TABLE grade_levels ADD UNIQUE KEY grade_levels_name_unique (name)');

        $now = now();
        foreach (['Rookie', 'Intermediate', 'Advance', 'Advance Economist'] as $i => $name) {
            DB::table('grade_levels')->updateOrInsert(
                ['name' => $name],
                ['sid' => null, 'sort_order' => $i + 1, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down()
    {
        DB::statement('ALTER TABLE grade_levels DROP INDEX grade_levels_name_unique');
        DB::statement('ALTER TABLE grade_levels ADD UNIQUE KEY grade_levels_sid_name_unique (sid, name)');
    }
};
