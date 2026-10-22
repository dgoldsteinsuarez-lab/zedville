<?php

namespace Tests\Feature\Admin;

use App\Models\GradeLevel;
use App\Models\LoginOption;
use App\Models\LoginQuestion;
use App\Models\Month;
use App\Models\User;
use Database\Seeders\RookieLearningContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-21: "in admin, i should be able to edit them" - the Log
 * In questions, which also feed the Academy's Monthly Questions.
 */
class LoginQuestionAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_list_can_be_filtered_to_one_levels_month(): void
    {
        $this->seed(RookieLearningContentSeeder::class);
        $admin = User::factory()->create(['role' => 1]);
        $rookie = GradeLevel::where('name', 'Rookie')->value('id');
        $month1 = Month::where('name', 'Month 1')->value('id');

        $response = $this->actingAs($admin)->get('/admin/login-question?level=' . $rookie . '&month=' . $month1);

        $response->assertOk();
        $response->assertSee('What does a direct deposit do?');
        $response->assertDontSee('What is simple interest?');
        // The right answer is shown in the list.
        $response->assertSee('Sends your salary straight into your bank account on payday');
    }

    public function test_an_admin_can_edit_a_seeded_question_and_its_right_answer(): void
    {
        $this->seed(RookieLearningContentSeeder::class);
        $admin = User::factory()->create(['role' => 1]);
        $question = LoginQuestion::with('options')->where('question', 'What does a direct deposit do?')->first();
        $options = $question->options->values();

        $response = $this->actingAs($admin)->post('/admin/login-question/loginQuestionStore', [
            'id' => $question->id,
            'school' => '',
            'grade_level_id' => $question->grade_level_id,
            'month_id' => $question->month_id,
            'question' => 'What does a direct deposit do for you?',
            'type' => 'mcq',
            'options' => $options->pluck('option_text')->all(),
            'option_ids' => $options->pluck('id')->all(),
            'correct' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('login_questions', ['id' => $question->id, 'question' => 'What does a direct deposit do for you?', 'sid' => null]);
        $this->assertTrue((bool) LoginOption::find($options[0]->id)->is_correct);
        $this->assertFalse((bool) LoginOption::find($options[1]->id)->is_correct);
    }
}
