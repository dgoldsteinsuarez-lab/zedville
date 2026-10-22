<?php

namespace Tests\Feature;

use App\Models\FinheroLibraryModule;
use App\Models\FinheroLibraryQuestion;
use App\Models\GradeLevel;
use App\Models\LoginQuestion;
use App\Models\Month;
use Database\Seeders\RookieLearningContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-21: the original Log In and Library questions, improved
 * and tied to the activity students do each month
 * (database/seeders/data/rookie_learning_content.json).
 */
class RookieLearningContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_rookie_month_from_1_to_8_gets_ten_log_in_questions_with_exactly_one_right_answer(): void
    {
        $this->seed(RookieLearningContentSeeder::class);
        $rookie = GradeLevel::where('name', 'Rookie')->first();

        foreach (range(1, 8) as $n) {
            $questions = LoginQuestion::with('options')
                ->where('grade_level_id', $rookie->id)
                ->where('month_id', Month::where('name', "Month {$n}")->value('id'))
                ->get();

            $this->assertCount(10, $questions, "Month {$n}");
            foreach ($questions as $question) {
                $this->assertSame(1, $question->options->where('is_correct', true)->count(), $question->question);
                $this->assertCount($question->type === 'mcq' ? 4 : 2, $question->options, $question->question);
            }
        }
    }

    public function test_the_library_keeps_the_original_module_numbers_and_adds_paying_your_bills(): void
    {
        $this->seed(RookieLearningContentSeeder::class);

        // finhero_monthly_settings.active_library_module_id and existing
        // 'library' points rows already use these numbers.
        $this->assertSame('Introduction to Budget', FinheroLibraryModule::find(1)->title);
        $this->assertSame('Introduction to Banking', FinheroLibraryModule::find(5)->title);
        $this->assertSame('Simple Interest', FinheroLibraryModule::find(6)->title);
        $this->assertSame('Fixed vs Variable Expenses', FinheroLibraryModule::find(7)->title);
        $this->assertSame('task_8', FinheroLibraryModule::where('title', 'Paying Your Bills')->value('module_key'));

        foreach (FinheroLibraryModule::with('questions.options')->get() as $module) {
            $this->assertCount(10, $module->questions, $module->title);
            $this->assertCount(2, $module->lesson, $module->title);
            foreach ($module->questions as $question) {
                $this->assertSame(1, $question->options->where('is_correct', true)->count(), $question->question_text);
            }
        }
    }

    public function test_running_the_seeder_again_adds_nothing(): void
    {
        $this->seed(RookieLearningContentSeeder::class);
        $this->seed(RookieLearningContentSeeder::class);

        $this->assertSame(80, LoginQuestion::count());
        $this->assertSame(80, FinheroLibraryQuestion::count());
        $this->assertSame(8, FinheroLibraryModule::count());
    }
}
