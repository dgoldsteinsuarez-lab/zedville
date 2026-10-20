<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ZEDVILLE — PISA
 *
 * Daniel, 2026-10-20: "PISA is the PISA exams that is done in many
 * countries, that includes financial literacy assessments. this is
 * separate from rookie level... any level can have this pisa practice
 * section... just add for pisa the difficulty or proficiency levels
 * that pisa officially has." Confirmed separate from grade_levels
 * (Rookie/Intermediate/Advance/Advance Economist, untouched here) - a
 * shared, global reference catalog (same convention as
 * 2026_10_19_000001_make_grade_levels_global.php), not per-school or
 * per-Grade, used later to tag/calibrate PISA-practice assignments.
 *
 * Seeded with the OECD's real Financial Literacy scale (the domain most
 * relevant to this platform) - 6 official bands with their real score
 * cut-offs (PISA scores run 0-1000). PISA also has Reading/Maths/Science
 * domains with their own scales - `domain` exists so those can be added
 * later without a schema change; not seeded now since Daniel only asked
 * for Financial Literacy's.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('pisa_proficiency_levels', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->default('Financial Literacy');
            $table->string('name');
            $table->unsignedTinyInteger('sort_order');
            $table->decimal('min_score', 6, 2)->nullable();
            $table->decimal('max_score', 6, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['domain', 'name']);
        });

        $now = now();
        $levels = [
            [
                'name' => 'Below Level 1',
                'sort_order' => 1,
                'min_score' => null,
                'max_score' => 325.57,
                'description' => 'Cannot reliably demonstrate even the most basic financial literacy skills assessed - cannot consistently identify common financial products or terms.',
            ],
            [
                'name' => 'Level 1',
                'sort_order' => 2,
                'min_score' => 325.57,
                'max_score' => 400.33,
                'description' => 'Can identify common financial products and terms, recognise the difference between needs and wants, and make simple decisions on everyday spending in contexts they are likely to have experienced personally.',
            ],
            [
                'name' => 'Level 2',
                'sort_order' => 3,
                'min_score' => 400.33,
                'max_score' => 475.10,
                'description' => 'Can apply knowledge of common financial products and commonly used financial terms to situations that are immediately relevant to them, and can recognise the value of a simple budget.',
            ],
            [
                'name' => 'Level 3',
                'sort_order' => 4,
                'min_score' => 475.10,
                'max_score' => 549.86,
                'description' => 'Can apply their understanding of commonly used financial concepts, terms and products to situations that are relevant to them. They begin to consider the consequences of financial decisions and can make simple financial plans in familiar contexts.',
            ],
            [
                'name' => 'Level 4',
                'sort_order' => 5,
                'min_score' => 549.86,
                'max_score' => 624.63,
                'description' => 'Can apply their understanding of financial terms and concepts to situations that may not arise until later in life, and can make financial decisions taking into account possible outside events, applying their understanding in contexts that are not immediately relevant to them.',
            ],
            [
                'name' => 'Level 5',
                'sort_order' => 6,
                'min_score' => 624.63,
                'max_score' => null,
                'description' => 'Can apply understanding of a wide range of financial terms and concepts to contexts that may only become relevant to their lives in the long term, analyse complex financial products, and take into account features of financial documents that are significant but unstated or not immediately evident, such as transaction costs. Can solve non-routine financial problems and describe the potential outcomes of financial decisions, showing understanding of the wider financial landscape, such as income tax.',
            ],
        ];

        foreach ($levels as $level) {
            DB::table('pisa_proficiency_levels')->insert(array_merge($level, [
                'domain' => 'Financial Literacy',
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('pisa_proficiency_levels');
    }
};
