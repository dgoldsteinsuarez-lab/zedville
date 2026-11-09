<?php

namespace Database\Seeders;

use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\CurriculumQuestion;
use App\Models\CurriculumQuestionOption;
use App\Models\PisaProficiencyLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * PISA practice activities (two per PISA proficiency level) and one real
 * end-of-year exam, from database/seeders/data/pisa_practice_content.json.
 * Original Zedville material inspired by the published PISA financial
 * literacy framework - not OECD material (see the disclaimer on the PISA pages).
 *
 * Safe to run more than once: an activity is matched by title, and its
 * questions are only added while it has none, so edits made in the admin
 * panel and students' attempts are never touched.
 *
 * The real exam is added INACTIVE: turn it on (and give it dates, so it goes
 * on every class calendar) in Admin > Curriculum > PISA Practice items.
 *
 *   php artisan db:seed --class=PisaPracticeContentSeeder
 */
class PisaPracticeContentSeeder extends Seeder
{
    public function run(): void
    {
        $content = json_decode(
            file_get_contents(database_path('seeders/data/pisa_practice_content.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->copyGraphics();

        $category = CurriculumCategory::firstOrCreate(
            ['is_pisa_practice' => true],
            ['grade_level_id' => null, 'name' => 'PISA Practice', 'sort_order' => 99]
        );

        $added = ['activities' => 0, 'questions' => 0];

        DB::transaction(function () use ($content, $category, &$added) {
            foreach ($content['practice'] as $index => $unit) {
                $levelId = PisaProficiencyLevel::where('name', $unit['level'])->value('id');
                if (!$levelId) {
                    $this->command?->warn("PISA level '{$unit['level']}' not found - skipped '{$unit['title']}'.");
                    continue;
                }

                $item = $this->item($category, $unit, [
                    'pisa_proficiency_level_id' => $levelId,
                    'is_real_exam' => false,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ], $added);
                $this->questions($item, $unit['questions'], $added);
            }

            $exam = $content['real_exam'];
            $item = $this->item($category, $exam, [
                'pisa_proficiency_level_id' => null,
                'is_real_exam' => true,
                'is_active' => false,
                'sort_order' => 100,
            ], $added);
            $this->questions($item, $exam['questions'], $added);
        });

        $this->command?->info("PISA content: {$added['activities']} activities and {$added['questions']} questions added.");
    }

    private function item(CurriculumCategory $category, array $unit, array $fields, array &$added): CurriculumItem
    {
        $item = CurriculumItem::firstOrCreate(
            ['category_id' => $category->id, 'title' => $unit['title']],
            $fields + [
                'description' => $unit['description'],
                'image' => $unit['image'] ?? null,
                'type' => 'quiz',
            ]
        );
        $added['activities'] += $item->wasRecentlyCreated ? 1 : 0;

        // An activity that already exists only follows its level and order
        // from the file (so a level correction reaches it) - its text,
        // questions and on/off switch stay as edited in the admin panel.
        if (!$item->wasRecentlyCreated) {
            $item->update([
                'pisa_proficiency_level_id' => $fields['pisa_proficiency_level_id'],
                'sort_order' => $fields['sort_order'],
            ]);
        }

        return $item;
    }

    private function questions(CurriculumItem $item, array $questions, array &$added): void
    {
        if ($item->questions()->exists()) {
            return;
        }

        foreach ($questions as $index => $q) {
            $question = CurriculumQuestion::create([
                'item_id' => $item->id,
                'question_text' => $q['question'],
                'explanation' => $q['explanation'] ?? null,
                'sort_order' => $index + 1,
            ]);

            foreach ($q['options'] as $optionIndex => $text) {
                CurriculumQuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $text,
                    'is_correct' => $optionIndex === $q['correct'],
                    'sort_order' => $optionIndex + 1,
                ]);
            }
            $added['questions']++;
        }
    }

    /** The graphics live with the seed data (public/uploads is not in git). */
    private function copyGraphics(): void
    {
        $target = public_path('uploads/curriculum');
        File::ensureDirectoryExists($target);

        foreach (File::files(database_path('seeders/data/pisa_graphics')) as $file) {
            File::copy($file->getPathname(), $target . DIRECTORY_SEPARATOR . $file->getFilename());
        }
    }
}
