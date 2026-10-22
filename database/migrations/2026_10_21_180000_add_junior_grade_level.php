<?php

use App\Models\GradeLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-10-21: "i think I should create a Junior Level, for grade 7
 * and 8, with more easy to digest content and activities" -> "build a
 * Junior." A fifth entry in the same global, admin-managed Levels list
 * every school already picks Grades from (Rookie/Intermediate/Advance/
 * Advance Economist) - not a parallel system. Inserted ahead of Rookie
 * (sort_order 1, shifting the rest up by one) since Junior is meant to be
 * the easiest/entry tier; existing Grades keep whatever Level they're
 * already assigned (nothing here reassigns a Grade - that stays the
 * per-school admin choice it already is, same as every other Level).
 *
 * Categories seeded with the same base set as every other Level
 * (Activities, Exams, Graphics, PISA Practice) - matching
 * 2026_10_20_180100_create_curriculum_categories_table.php's convention
 * exactly, including is_pisa_practice. No Junior-specific extra category
 * yet (Daniel's "different languages... Language for Beginner" needs one
 * more clarifying answer before it's modeled - see bugs.md - so this
 * migration doesn't guess at it).
 */
return new class extends Migration
{
    public function up()
    {
        $now = now();

        DB::table('grade_levels')
            ->whereIn('name', ['Rookie', 'Intermediate', 'Advance', 'Advance Economist'])
            ->increment('sort_order');

        DB::table('grade_levels')->updateOrInsert(
            ['name' => 'Junior'],
            ['sid' => null, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now]
        );

        $junior = GradeLevel::where('name', 'Junior')->first();

        if (!DB::table('curriculum_categories')->where('grade_level_id', $junior->id)->exists()) {
            $sort = 1;
            foreach (['Activities', 'Exams', 'Graphics'] as $name) {
                DB::table('curriculum_categories')->insert([
                    'grade_level_id' => $junior->id, 'name' => $name, 'is_pisa_practice' => false,
                    'sort_order' => $sort++, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            DB::table('curriculum_categories')->insert([
                'grade_level_id' => $junior->id, 'name' => 'PISA Practice', 'is_pisa_practice' => true,
                'sort_order' => $sort, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        $junior = DB::table('grade_levels')->where('name', 'Junior')->first();

        if ($junior) {
            DB::table('grades')->where('grade_level_id', $junior->id)->update(['grade_level_id' => null]);
            DB::table('curriculum_categories')->where('grade_level_id', $junior->id)->delete();
            DB::table('grade_levels')->where('id', $junior->id)->delete();
        }

        DB::table('grade_levels')
            ->whereIn('name', ['Rookie', 'Intermediate', 'Advance', 'Advance Economist'])
            ->decrement('sort_order');
    }
};
