<?php

namespace Database\Seeders;

use App\Models\FinheroLibraryModule;
use App\Models\FinheroLibraryOption;
use App\Models\FinheroLibraryQuestion;
use App\Models\GradeLevel;
use App\Models\LoginOption;
use App\Models\LoginQuestion;
use App\Models\Month;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Rookie Log In questions (one set per progression month) and the FinHero
 * Library modules (lesson + quiz), from database/seeders/data/
 * rookie_learning_content.json.
 *
 * Safe to run more than once: modules are upserted by id, and a question is
 * only added if the same text isn't already there, so existing questions
 * (and students' attempts on them) are never touched. Editing a question's
 * text in the JSON therefore adds it as a new question - delete the old one
 * in the admin panel.
 *
 *   php artisan db:seed --class=RookieLearningContentSeeder
 */
class RookieLearningContentSeeder extends Seeder
{
    public function run(): void
    {
        $content = json_decode(
            file_get_contents(database_path('seeders/data/rookie_learning_content.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $added = ['modules' => 0, 'library' => 0, 'login' => 0];

        DB::transaction(function () use ($content, &$added) {
            foreach ($content['library_modules'] as $module) {
                $row = FinheroLibraryModule::updateOrCreate(['id' => $module['id']], [
                    'module_key' => $module['key'],
                    'title' => $module['title'],
                    'icon' => $module['icon'],
                    'sort_order' => $module['month'],
                    'lesson' => $module['lesson'],
                ]);
                $added['modules'] += $row->wasRecentlyCreated ? 1 : 0;

                foreach ($module['questions'] as $index => $q) {
                    if (FinheroLibraryQuestion::where('module_id', $row->id)->where('question_text', $q['question'])->exists()) {
                        continue;
                    }

                    $question = FinheroLibraryQuestion::create([
                        'module_id' => $row->id,
                        'question_text' => $q['question'],
                        'sort_order' => $index + 1,
                    ]);

                    foreach ($q['options'] as $optionIndex => $text) {
                        FinheroLibraryOption::create([
                            'question_id' => $question->id,
                            'option_text' => $text,
                            'is_correct' => $optionIndex === $q['correct'],
                            'sort_order' => $optionIndex + 1,
                        ]);
                    }
                    $added['library']++;
                }
            }

            $levelId = GradeLevel::where('name', $content['level'])->value('id');

            foreach ($content['login_months'] as $month) {
                $monthId = Month::where('name', "Month {$month['month']}")->value('id');

                foreach ($month['questions'] as $q) {
                    $exists = LoginQuestion::where('grade_level_id', $levelId)
                        ->where('month_id', $monthId)
                        ->where('question', $q['question'])
                        ->exists();
                    if ($exists) {
                        continue;
                    }

                    $question = LoginQuestion::create([
                        'sid' => null,
                        'grade_level_id' => $levelId,
                        'month_id' => $monthId,
                        'question' => $q['question'],
                        'type' => $q['type'],
                    ]);

                    foreach ($q['options'] as $optionIndex => $text) {
                        LoginOption::create([
                            'question_id' => $question->id,
                            'option_text' => $text,
                            'is_correct' => $optionIndex === $q['correct'],
                        ]);
                    }
                    $added['login']++;
                }
            }
        });

        $this->command?->info(sprintf(
            'Library modules created: %d, Library questions added: %d, Log In questions added: %d',
            $added['modules'],
            $added['library'],
            $added['login']
        ));
    }
}
