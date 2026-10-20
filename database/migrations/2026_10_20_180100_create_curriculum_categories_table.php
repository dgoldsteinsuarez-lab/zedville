<?php

use App\Models\GradeLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ZEDVILLE — Curriculum categories ("Academy")
 *
 * Daniel, 2026-10-20, pasting his own Sept-13 planning doc ("Zedville_Admin
 * Structure_Proposal_v1"), §8/§9: each Grade Level (Rookie, Intermediate,
 * Advance, Advance Economist) should have Activities, Exams, Graphics, plus
 * level-specific extra topics (Intermediate: Insurance, Emergency Costs,
 * Credit; Advance: Competition, Inflation; Advance Economist: Voting).
 * "PISA Practice" is added to every level too (not a verbatim doc item) so
 * his earlier "any level can have this pisa practice section" is usable
 * without having to add it by hand first.
 *
 * `grade_level_id` is deliberately a plain indexed column, not a foreign
 * key - matching `grades.grade_level_id`/`classes.grade_level_id`, neither
 * of which is a real FK either. AdminDashboardController::deleteGradeLevel()
 * unassigns Grades rather than cascading ("Grades just become 'unassigned';
 * nothing else is lost") - a cascading FK here would instead silently wipe
 * every category/item/question/option and every student's quiz history for
 * that level, the opposite of this app's established rule for this exact
 * relationship.
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('curriculum_categories')) {
            return;
        }

        Schema::create('curriculum_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grade_level_id')->index();
            $table->string('name');
            $table->boolean('is_pisa_practice')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['grade_level_id', 'name']);
        });

        $now = now();
        $base = ['Activities', 'Exams', 'Graphics'];
        $extra = [
            'Rookie' => [],
            'Intermediate' => ['Insurance', 'Emergency Costs', 'Credit'],
            'Advance' => ['Competition', 'Inflation'],
            'Advance Economist' => ['Voting'],
        ];

        foreach (GradeLevel::all() as $level) {
            $names = array_merge($base, $extra[$level->name] ?? []);
            $sort = 1;
            foreach ($names as $name) {
                DB::table('curriculum_categories')->insert([
                    'grade_level_id' => $level->id,
                    'name' => $name,
                    'is_pisa_practice' => false,
                    'sort_order' => $sort++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            DB::table('curriculum_categories')->insert([
                'grade_level_id' => $level->id,
                'name' => 'PISA Practice',
                'is_pisa_practice' => true,
                'sort_order' => $sort,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('curriculum_categories');
    }
};
