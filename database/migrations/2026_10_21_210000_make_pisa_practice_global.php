<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-10-21: "for PISA i don't need to divide in Zedville levels,
 * only the PISA levels... independent from if a student is Junior, Rookie
 * or else, they will have the same activities because its for PISA and
 * they just have these." Reverses this session's earlier design (one
 * PISA Practice category per Zedville Grade Level) - PISA content becomes
 * one single global pool, organized only by the real PisaProficiencyLevel,
 * visible to every student regardless of their Grade Level.
 *
 * No real items exist yet under any of the 5 PISA Practice categories
 * (confirmed live before writing this), so this is a structural
 * consolidation, not a real data migration - but written to actually
 * move any items first, not just delete, in case it ever runs against a
 * database that already has some.
 *
 * `curriculum_categories.grade_level_id` has to become nullable to
 * represent "not scoped to one Level" - doctrine/dbal isn't installed in
 * this project, so a raw ALTER does the nullability change (same
 * constraint noted elsewhere in this app's migrations).
 */
return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE curriculum_categories MODIFY grade_level_id BIGINT UNSIGNED NULL');

        $pisaCategories = DB::table('curriculum_categories')->where('is_pisa_practice', true)->orderBy('id')->get();

        if ($pisaCategories->isEmpty()) {
            return;
        }

        $keep = $pisaCategories->first();
        DB::table('curriculum_categories')->where('id', $keep->id)
            ->update(['grade_level_id' => null, 'name' => 'PISA Practice']);

        $otherIds = $pisaCategories->skip(1)->pluck('id');
        if ($otherIds->isNotEmpty()) {
            DB::table('curriculum_items')->whereIn('category_id', $otherIds)->update(['category_id' => $keep->id]);
            DB::table('curriculum_categories')->whereIn('id', $otherIds)->delete();
        }
    }

    public function down()
    {
        // Not reversible to the original per-Level split (which Level
        // would a global item go back to?) and the global PISA category
        // row itself now has a NULL grade_level_id, so restoring the NOT
        // NULL constraint here would just fail against it - leaves the
        // single global category and the nullable column in place rather
        // than guessing at a revert.
    }
};
